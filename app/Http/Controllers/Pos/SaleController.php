<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DiscountRule;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\ProductStore;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Shift;
use App\Services\JournalService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private JournalService $journalService,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $openShift = Shift::where('user_id', $request->user()->id)->where('status', 'open')->first();
        if (! $openShift) {
            return redirect()->route('shifts.open-form');
        }

        $store = current_store();
        if (! $store) {
            return redirect()->route('dashboard')->with('error', 'No store assigned to your account. Contact an administrator.');
        }

        $defaultTaxGroup = \App\Models\TaxGroup::with('components')->where('is_default', true)->first();

        $products = Product::with([
                'serials' => fn ($q) => $q->where('status', 'in_stock'),
                'category.taxGroup.components',
                'taxGroup.components',
                'productStores' => fn ($q) => $q->where('store_id', $store->id),
                'unit',
            ])
            ->where('is_active', true)
            ->whereHas('productStores', fn ($q) => $q->where('store_id', $store->id)->where('quantity', '>', 0))
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'selling_price' => (float) $p->selling_price,
                'quantity' => $p->quantity,
                'unit' => $p->unit?->abbreviation ?? $p->unit?->name,
                'image_url' => $p->image_url,
                'track_serial' => $p->track_serial,
                'category_id' => $p->category_id,
                'category_name' => $p->category?->name ?? 'Uncategorized',
                'tax_rate' => (float) (($p->taxGroup ?? $p->category?->taxGroup ?? $defaultTaxGroup)?->totalRate() ?? 0),
                'serials' => $p->track_serial
                    ? $p->serials->map(fn ($s) => ['id' => $s->id, 'imei_serial' => $s->imei_serial])->values()
                    : [],
            ]);

        // Category filter pills — counts are derived from $products itself (already
        // scoped to this store's in-stock, active items) so the numbers shown always
        // match what's actually orderable, rather than a separately-scoped DB count.
        $categories = $products->groupBy('category_name')
            ->map(fn ($group, $name) => ['name' => $name, 'count' => $group->count()])
            ->sortBy('name')
            ->values();

        // Quick picks: best-sellers at this store over the last 30 days, capped at 8.
        // Falls back to the first 8 products alphabetically for a fresh store with no sales yet.
        $topSellingIds = SaleItem::selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->where('sales.store_id', $store->id)
            ->where('sales.sold_at', '>=', now()->subDays(30))
            ->groupBy('sale_items.product_id')
            ->orderByDesc('qty')
            ->limit(8)
            ->pluck('sale_items.product_id');

        $quickPicks = $topSellingIds->isNotEmpty()
            ? $topSellingIds->map(fn ($id) => $products->firstWhere('id', $id))->filter()->values()
            : $products->take(8)->values();

        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']);

        $discountRules = DiscountRule::active()->get()->map(fn (DiscountRule $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'type' => $r->type,
            'value' => (float) $r->value,
            'scope' => $r->scope,
            'scope_id' => $r->scope_id,
            'min_quantity' => $r->min_quantity,
        ]);

        $resumeSale = null;
        if ($request->filled('resume')) {
            $resumeSale = Sale::with('items')
                ->where('status', 'held')
                ->findOrFail($request->integer('resume'));
        }

        $heldCount = Sale::where('status', 'held')->count();

        $bankDetails = [
            'account_name' => Setting::get('bank_account_name', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('bank_account_number', ''),
        ];

        return view('pos.index', compact('products', 'categories', 'quickPicks', 'customers', 'resumeSale', 'heldCount', 'discountRules', 'bankDetails'));
    }

    public function hold(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => json_decode($request->input('items_json', '[]'), true) ?? [],
        ]);

        $validated = $request->validate([
            'resume_sale_id' => ['nullable', 'exists:sales,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_serial_id' => ['nullable', 'exists:product_serials,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $discount = (float) ($item['discount_amount'] ?? 0);
                $unitPrice = (float) $product->selling_price;
                $lineSubtotal = ($unitPrice * $quantity) - $discount;

                $subtotal += $lineSubtotal;
                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_serial_id' => $item['product_serial_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $openShift = Shift::where('user_id', $request->user()->id)->where('status', 'open')->first();

            $saleAttributes = [
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => $request->user()->id,
                'store_id' => current_store()?->id,
                'terminal_id' => $openShift?->terminal_id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'change_due' => 0,
                'status' => 'held',
                'sold_at' => null,
            ];

            if (! empty($validated['resume_sale_id'])) {
                $sale = Sale::where('status', 'held')->findOrFail($validated['resume_sale_id']);
                $sale->update($saleAttributes);
                $sale->items()->delete();
            } else {
                $sale = Sale::create([...$saleAttributes, 'invoice_number' => $this->generateInvoiceNumber()]);
            }

            foreach ($itemsData as $data) {
                $sale->items()->create($data);
            }
        });

        return redirect()->route('pos.index')->with('success', 'Order held. No stock or payment was recorded yet.');
    }

    /**
     * Completes a sale — used both for a normal live checkout and for replaying a
     * sale that was queued locally (IndexedDB) while the POS was offline. Must be
     * idempotent on client_uuid (the client may retry). Live checkouts still fail
     * hard on stock conflicts, same as before; queued (was_queued=true) checkouts
     * never hard-fail on a conflict — the physical sale already happened, so a
     * shortfall is recorded via needs_review for a manager to reconcile instead.
     */
    public function sync(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->merge([
            'items' => json_decode($request->input('items_json', '[]'), true) ?? [],
            'payments' => json_decode($request->input('payments_json', '[]'), true) ?? [],
        ]);

        $validated = $request->validate([
            'client_uuid' => ['required', 'string', 'max:100'],
            'client_sold_at' => ['required', 'date'],
            'was_queued' => ['boolean'],
            'resume_sale_id' => ['nullable', 'exists:sales,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_serial_id' => ['nullable', 'exists:product_serials,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'in:cash,card,transfer'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        $existing = Sale::where('client_uuid', $validated['client_uuid'])->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'invoice_number' => $existing->invoice_number,
                'receipt_url' => route('sales.receipt', $existing),
            ]);
        }

        // Defensive re-check: create() already gates the POS screen behind an
        // open shift, but a session that's gone stale mid-shift-close shouldn't
        // be able to silently post a shift-less sale.
        $shift = Shift::where('user_id', $request->user()->id)->where('status', 'open')->first();
        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No open shift found — please start a shift before completing a sale.',
            ], 422);
        }

        $store = current_store();
        if (! $store) {
            return response()->json([
                'success' => false,
                'message' => 'No store assigned to your account. Contact an administrator.',
            ], 422);
        }

        $wasQueued = $request->boolean('was_queued');

        $sale = DB::transaction(function () use ($validated, $request, $wasQueued, $shift, $store) {
            $subtotal = 0;
            $itemsData = [];
            $reviewNotes = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $discount = (float) ($item['discount_amount'] ?? 0);
                $unitPrice = (float) $product->selling_price;
                $lineSubtotal = ($unitPrice * $quantity) - $discount;

                $stockRow = ProductStore::where('product_id', $product->id)
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->first();
                $availableQuantity = $stockRow->quantity ?? 0;

                if (! $wasQueued && $availableQuantity < $quantity) {
                    throw ValidationException::withMessages(['items' => "Insufficient stock for {$product->name}."]);
                }

                $serial = null;

                if ($product->track_serial) {
                    if (! empty($item['product_serial_id'])) {
                        $serial = ProductSerial::where('id', $item['product_serial_id'])
                            ->where('product_id', $product->id)
                            ->where('status', 'in_stock')
                            ->lockForUpdate()
                            ->first();
                    }

                    if (! $serial) {
                        if (! $wasQueued) {
                            throw ValidationException::withMessages(['items' => "Selected serial for {$product->name} is no longer available."]);
                        }
                        $reviewNotes[] = "Serial/IMEI for {$product->name} was already sold by the time this offline sale synced — recorded without a serial link.";
                    }
                } elseif ($wasQueued && $availableQuantity < $quantity) {
                    $reviewNotes[] = "{$product->name} oversold by ".($quantity - $availableQuantity)." unit(s) — stock went negative reconciling this offline sale.";
                }

                $subtotal += $lineSubtotal;
                $itemsData[] = compact('product', 'quantity', 'discount', 'unitPrice', 'lineSubtotal', 'serial');
            }

            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $amountPaid = collect($validated['payments'])->sum('amount');

            if ($amountPaid < $totalAmount) {
                throw ValidationException::withMessages(['payments' => 'Amount paid is less than the total due.']);
            }

            $saleAttributes = [
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => $request->user()->id,
                'store_id' => $store->id,
                'terminal_id' => $shift->terminal_id,
                'shift_id' => $shift->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'change_due' => max(0, $amountPaid - $totalAmount),
                'status' => 'completed',
                'sold_at' => $validated['client_sold_at'],
                'client_uuid' => $validated['client_uuid'],
                'offline_queued_at' => $wasQueued ? $validated['client_sold_at'] : null,
                'needs_review' => ! empty($reviewNotes),
                'review_note' => $reviewNotes ? implode(' ', $reviewNotes) : null,
            ];

            if (! empty($validated['resume_sale_id'])) {
                $sale = Sale::where('status', 'held')->lockForUpdate()->findOrFail($validated['resume_sale_id']);
                $sale->update($saleAttributes);
                $sale->items()->delete();
            } else {
                $sale = Sale::create([...$saleAttributes, 'invoice_number' => $this->generateInvoiceNumber()]);
            }

            foreach ($itemsData as $data) {
                $saleItem = $sale->items()->create([
                    'product_id' => $data['product']->id,
                    'product_serial_id' => $data['serial']?->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $data['unitPrice'],
                    'discount_amount' => $data['discount'],
                    'subtotal' => $data['lineSubtotal'],
                ]);

                $this->stockService->sell(
                    $data['product'],
                    $store,
                    $data['quantity'],
                    $request->user(),
                    $saleItem,
                    $data['serial'],
                    $wasQueued ? 'Synced from offline queue' : null,
                );
            }

            foreach ($validated['payments'] as $payment) {
                $sale->payments()->create([
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference_no' => $payment['reference_no'] ?? null,
                    'paid_at' => $validated['client_sold_at'],
                ]);
            }

            $this->postSaleJournal($sale, $itemsData);

            return $sale;
        });

        return response()->json([
            'success' => true,
            'invoice_number' => $sale->invoice_number,
            'receipt_url' => route('sales.receipt', $sale),
        ]);
    }

    /**
     * Revenue/cash leg (this controller has the payment/discount/tax context
     * StockService doesn't) plus the COGS/inventory leg (using each item's
     * cost basis, available here via $itemsData). Change given is always cash
     * out of the drawer regardless of how the sale was paid, so it's netted
     * out of the cash debit rather than the card/transfer debit.
     */
    private function postSaleJournal(Sale $sale, array $itemsData): void
    {
        $payments = $sale->payments()->get();
        $cash = (float) $payments->where('method', 'cash')->sum('amount') - (float) $sale->change_due;
        $bank = (float) $payments->whereIn('method', ['card', 'transfer'])->sum('amount');

        $lines = [];
        if (round($cash, 2) != 0) {
            $lines[] = ['account' => '1000', 'debit' => round($cash, 2)];
        }
        if (round($bank, 2) != 0) {
            $lines[] = ['account' => '1010', 'debit' => round($bank, 2)];
        }

        $revenue = round((float) $sale->subtotal - (float) $sale->discount_amount, 2);
        if ($revenue != 0) {
            $lines[] = ['account' => '4000', 'credit' => $revenue];
        }
        if ((float) $sale->tax_amount > 0) {
            $lines[] = ['account' => '2100', 'credit' => round((float) $sale->tax_amount, 2)];
        }

        if (! empty($lines)) {
            $this->journalService->postEntry($lines, 'Sale '.$sale->invoice_number, $sale, $sale->sold_at);
        }

        $cogs = round(collect($itemsData)->sum(fn ($d) => (float) $d['product']->cost_price * $d['quantity']), 2);
        if ($cogs > 0) {
            $this->journalService->postEntry([
                ['account' => '5000', 'debit' => $cogs],
                ['account' => '1200', 'credit' => $cogs],
            ], 'COGS for sale '.$sale->invoice_number, $sale, $sale->sold_at);
        }
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Sale::where('invoice_number', $number)->exists());

        return $number;
    }
}
