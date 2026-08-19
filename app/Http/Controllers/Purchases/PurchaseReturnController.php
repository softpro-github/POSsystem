<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\ReturnReason;
use App\Services\JournalService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private JournalService $journalService,
    ) {}

    public function create(PurchaseOrder $purchaseOrder): View
    {
        if ($purchaseOrder->status !== 'received') {
            abort(400, 'Only received purchase orders can be returned.');
        }

        $purchaseOrder->load('items.product', 'supplier');
        $returnReasons = ReturnReason::where('is_active', true)->orderBy('name')->get();

        return view('purchases.returns.create', compact('purchaseOrder', 'returnReasons'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'received') {
            return back()->with('error', 'Only received purchase orders can be returned.');
        }

        if (! $purchaseOrder->store && ! current_store()) {
            return back()->with('error', 'This purchase order has no store assigned and none is currently selected.');
        }

        $validated = $request->validate([
            'return_reason_id' => ['required', 'exists:return_reasons,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $purchaseOrder->load('items.product');
        $store = $purchaseOrder->store ?? current_store();

        DB::transaction(function () use ($validated, $purchaseOrder, $request, $store) {
            $totalAmount = 0;
            $purchaseReturn = null;

            foreach ($validated['items'] as $itemId => $data) {
                $quantity = (int) $data['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $item = $purchaseOrder->items->firstWhere('id', (int) $itemId);

                if (! $item) {
                    throw ValidationException::withMessages(['items' => 'Invalid line item.']);
                }

                if ($quantity > $item->returnableQuantity()) {
                    throw ValidationException::withMessages(['items' => "Cannot return more than the remaining quantity for {$item->product->name}."]);
                }

                if ($purchaseReturn === null) {
                    $purchaseReturn = $purchaseOrder->returns()->create([
                        'user_id' => $request->user()->id,
                        'return_reason_id' => $validated['return_reason_id'],
                        'notes' => $validated['notes'] ?? null,
                        'total_amount' => 0,
                    ]);
                }

                $subtotal = round($item->unit_cost * $quantity, 2);

                $purchaseReturn->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $subtotal,
                ]);

                $item->increment('quantity_returned', $quantity);

                $this->stockService->returnPurchase(
                    $item->product,
                    $store,
                    $quantity,
                    $request->user(),
                    $item,
                    'Return to supplier: PO #'.$purchaseOrder->id.' — '.$purchaseReturn->returnReason->name,
                );

                $totalAmount += $subtotal;
            }

            if ($purchaseReturn === null) {
                throw ValidationException::withMessages(['items' => 'Select at least one item and quantity to return.']);
            }

            $purchaseReturn->update(['total_amount' => $totalAmount]);

            $purchaseOrder->supplier->decrement('balance', $totalAmount);

            $this->journalService->postEntry(
                lines: [
                    ['account' => '2000', 'debit' => $totalAmount, 'credit' => 0],
                    ['account' => '1200', 'debit' => 0, 'credit' => $totalAmount],
                ],
                description: 'Return to supplier: PO #'.$purchaseOrder->id,
                reference: $purchaseReturn,
            );
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Return to supplier processed and stock updated.');
    }
}
