<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentReason;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'delta' => ['required', 'integer', 'not_in:0'],
            'adjustment_reason_id' => ['required', 'exists:adjustment_reasons,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $store = current_store();
        if (! $store) {
            return back()->with('error', 'No store assigned to your account. Contact an administrator.');
        }

        $product = Product::findOrFail($validated['product_id']);
        $reason = AdjustmentReason::findOrFail($validated['adjustment_reason_id']);

        if ($validated['delta'] < 0 && $product->quantity + $validated['delta'] < 0) {
            return back()->with('error', 'Adjustment would result in negative stock.');
        }

        $note = $reason->name.($validated['note'] ? ' — '.$validated['note'] : '');

        $this->stockService->adjust($product, $store, $validated['delta'], $request->user(), $note, $reason);

        return back()->with('success', "Stock adjusted for {$product->name}.");
    }
}
