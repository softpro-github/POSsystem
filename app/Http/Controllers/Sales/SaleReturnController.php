<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\ReturnReason;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\JournalService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private JournalService $journalService,
    ) {}

    public function create(Sale $sale): View
    {
        if ($sale->status !== 'completed') {
            abort(400, 'Only completed sales can be returned.');
        }

        $sale->load(['items.product', 'items.productSerial', 'returns.items']);
        $returnReasons = ReturnReason::where('is_active', true)->orderBy('name')->get();

        return view('sales.returns.create', compact('sale', 'returnReasons'));
    }

    public function store(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'completed') {
            return back()->with('error', 'Only completed sales can be returned.');
        }

        if (! $sale->store && ! current_store()) {
            return back()->with('error', 'This sale has no store assigned and none is currently selected.');
        }

        $validated = $request->validate([
            'return_reason_id' => ['required', 'exists:return_reasons,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $sale->load('items.product', 'items.productSerial');
        $store = $sale->store ?? current_store();

        DB::transaction(function () use ($validated, $sale, $request, $store) {
            $totalRefunded = 0;
            $costBasis = 0;
            $saleReturn = null;

            foreach ($validated['items'] as $saleItemId => $data) {
                $quantity = (int) $data['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $saleItem = $sale->items->firstWhere('id', (int) $saleItemId);

                if (! $saleItem) {
                    throw ValidationException::withMessages(['items' => 'Invalid sale item.']);
                }

                if ($quantity > $saleItem->returnableQuantity()) {
                    throw ValidationException::withMessages(['items' => "Cannot return more than remaining quantity for {$saleItem->product->name}."]);
                }

                if ($saleItem->product_serial_id && $quantity !== $saleItem->returnableQuantity()) {
                    throw ValidationException::withMessages(['items' => "Serialized items must be returned in full for {$saleItem->product->name}."]);
                }

                if ($saleReturn === null) {
                    $saleReturn = $sale->returns()->create([
                        'user_id' => $request->user()->id,
                        'return_reason_id' => $validated['return_reason_id'],
                        'reason' => $validated['reason'] ?? null,
                        'total_refunded' => 0,
                    ]);
                }

                $perUnitRefund = $saleItem->quantity > 0 ? $saleItem->subtotal / $saleItem->quantity : 0;
                $refundAmount = round($perUnitRefund * $quantity, 2);

                $saleReturn->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'quantity' => $quantity,
                    'refund_amount' => $refundAmount,
                ]);

                $saleItem->increment('returned_quantity', $quantity);

                $this->stockService->returnStock(
                    $saleItem->product,
                    $store,
                    $quantity,
                    $request->user(),
                    $saleItem,
                    $saleItem->product_serial_id ? $saleItem->productSerial : null,
                    'Return: '.$sale->invoice_number.' — '.$saleReturn->returnReason->name,
                );

                $totalRefunded += $refundAmount;
                $costBasis += $saleItem->product->cost_price * $quantity;
            }

            if ($saleReturn === null) {
                throw ValidationException::withMessages(['items' => 'Select at least one item and quantity to return.']);
            }

            $saleReturn->update(['total_refunded' => $totalRefunded]);

            $sale->refresh();
            $newRefundedAmount = $sale->refunded_amount + $totalRefunded;
            $fullyReturned = $sale->items()->get()->every(fn ($item) => $item->returned_quantity >= $item->quantity);

            $sale->update([
                'refunded_amount' => $newRefundedAmount,
                'status' => $fullyReturned ? 'refunded' : $sale->status,
            ]);

            $this->postReturnJournal($saleReturn, $sale, $totalRefunded, $costBasis);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Return processed and stock restored.');
    }

    private function postReturnJournal(SaleReturn $saleReturn, Sale $sale, float $totalRefunded, float $costBasis): void
    {
        if ($totalRefunded > 0) {
            $this->journalService->postEntry(
                lines: [
                    ['account' => '4100', 'debit' => $totalRefunded, 'credit' => 0],
                    ['account' => '1000', 'debit' => 0, 'credit' => $totalRefunded],
                ],
                description: 'Sale return: '.$sale->invoice_number,
                reference: $saleReturn,
            );
        }

        if ($costBasis > 0) {
            $this->journalService->postEntry(
                lines: [
                    ['account' => '1200', 'debit' => $costBasis, 'credit' => 0],
                    ['account' => '5000', 'debit' => 0, 'credit' => $costBasis],
                ],
                description: 'Sale return (inventory restored): '.$sale->invoice_number,
                reference: $saleReturn,
            );
        }
    }
}
