<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentReason;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxGroup;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store = current_store();

        $products = Product::with([
                'category',
                'productStores' => fn ($q) => $q->when($store, fn ($q) => $q->where('store_id', $store->id)),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%");
                });
            })
            ->when($request->boolean('low_stock'), fn ($query) => $query->lowStock())
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $adjustmentReasons = AdjustmentReason::where('is_active', true)->orderBy('name')->get();

        return view('inventory.products.index', compact('products', 'adjustmentReasons'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $taxGroups = TaxGroup::where('is_active', true)->orderBy('name')->get();

        return view('inventory.products.create', compact('categories', 'brands', 'units', 'taxGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $taxGroups = TaxGroup::where('is_active', true)->orderBy('name')->get();

        return view('inventory.products.edit', compact('product', 'categories', 'brands', 'units', 'taxGroups'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validated($request, $product);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = null;
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('success', 'Product has sales/stock history, so it was deactivated instead of deleted.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'tax_group_id' => ['nullable', 'exists:tax_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'.($product ? ",{$product->id}" : '')],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'.($product ? ",{$product->id}" : '')],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'track_serial' => ['boolean'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['track_serial'] = $request->boolean('track_serial');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sku'] = ($validated['sku'] ?? '') !== '' ? $validated['sku'] : null;

        return $validated;
    }
}
