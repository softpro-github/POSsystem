<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);

        return view('inventory.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('inventory.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255', 'unique:brands,name']]);

        Brand::create($request->only('name'));

        return redirect()->route('brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        return view('inventory.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255', 'unique:brands,name,'.$brand->id]]);

        $brand->update($request->only('name'));

        return redirect()->route('brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'Cannot delete a brand that has products.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted.');
    }
}
