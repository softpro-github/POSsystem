<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'barcode', 'selling_price']);
        $recentPurchaseOrders = PurchaseOrder::where('status', 'received')
            ->latest('received_date')
            ->limit(10)
            ->get(['id', 'received_date']);

        return view('inventory.labels.create', compact('products', 'recentPurchaseOrders'));
    }

    public function fromPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $items = $purchaseOrder->items()->with('product')->where('quantity_received', '>', 0)->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'sku' => $item->product->sku,
                'barcode' => $item->product->barcode,
                'price' => (float) $item->product->selling_price,
                'quantity' => $item->quantity_received,
            ]);

        return response()->json($items);
    }

    public function print(Request $request): View
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $labels = collect($validated['items'])->flatMap(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                return [];
            }

            return array_fill(0, (int) $item['quantity'], $product);
        });

        $items = $validated['items'];

        return view('inventory.labels.print', compact('labels', 'items'));
    }
}
