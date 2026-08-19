<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\JournalService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private JournalService $journalService,
    ) {}

    public function index(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('order_date')
            ->paginate(20)
            ->withQueryString();

        return view('purchases.index', compact('purchaseOrders'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']);

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => json_decode($request->input('items_json', '[]'), true) ?? [],
        ]);

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated, $request) {
            $total = collect($validated['items'])->sum(fn ($item) => $item['quantity_ordered'] * $item['unit_cost']);

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'store_id' => current_store()?->id,
                'status' => 'pending',
                'order_date' => $validated['order_date'],
                'total_amount' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity_ordered'] * $item['unit_cost'],
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product', 'returns.returnReason']);

        return view('purchases.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'This purchase order has already been processed.');
        }

        $store = $purchaseOrder->store ?? current_store();
        if (! $store) {
            return back()->with('error', 'This purchase order has no store assigned and none is currently selected.');
        }

        $purchaseOrder->load('items.product');

        $rules = ['items' => ['required', 'array']];
        foreach ($purchaseOrder->items as $item) {
            $rules["items.{$item->id}.quantity_received"] = ['required', 'integer', 'min:0', 'max:'.$item->quantity_ordered];
            if ($item->product->track_serial) {
                $rules["items.{$item->id}.serials"] = ['nullable', 'string'];
            }
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $purchaseOrder, $request, $store) {
            $receivedValue = 0;

            foreach ($purchaseOrder->items as $item) {
                $data = $validated['items'][$item->id] ?? null;
                $quantityReceived = (int) ($data['quantity_received'] ?? 0);

                if ($quantityReceived <= 0) {
                    continue;
                }

                $serials = [];
                if ($item->product->track_serial) {
                    $serials = collect(preg_split('/[\r\n,]+/', (string) ($data['serials'] ?? '')))
                        ->map(fn ($s) => trim($s))
                        ->filter()
                        ->values()
                        ->all();

                    if (count($serials) !== $quantityReceived) {
                        throw ValidationException::withMessages([
                            'items' => "Enter exactly {$quantityReceived} serial/IMEI number(s) for {$item->product->name}.",
                        ]);
                    }
                }

                $this->stockService->receivePurchase(
                    $item->product,
                    $store,
                    $quantityReceived,
                    $request->user(),
                    $item,
                    $serials,
                    'PO Receipt: '.$purchaseOrder->id,
                );

                $item->increment('quantity_received', $quantityReceived);

                $receivedValue += $quantityReceived * $item->unit_cost;
            }

            $purchaseOrder->update([
                'status' => 'received',
                'received_date' => now(),
            ]);

            if ($receivedValue > 0) {
                $purchaseOrder->supplier->increment('balance', $receivedValue);

                $this->journalService->postEntry(
                    lines: [
                        ['account' => '1200', 'debit' => $receivedValue, 'credit' => 0],
                        ['account' => '2000', 'debit' => 0, 'credit' => $receivedValue],
                    ],
                    description: 'PO Receipt: '.$purchaseOrder->id,
                    reference: $purchaseOrder,
                );
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Stock received and inventory updated.');
    }
}
