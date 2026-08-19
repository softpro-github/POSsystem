<?php

namespace App\Http\Controllers\DiscountRules;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountRule;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountRuleController extends Controller
{
    public function index(): View
    {
        $discountRules = DiscountRule::orderBy('name')->paginate(20);

        return view('discount-rules.index', compact('discountRules'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('discount-rules.create', compact('categories', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        DiscountRule::create($this->validated($request));

        return redirect()->route('discount-rules.index')->with('success', 'Discount rule created.');
    }

    public function edit(DiscountRule $discountRule): View
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('discount-rules.edit', compact('discountRule', 'categories', 'products'));
    }

    public function update(Request $request, DiscountRule $discountRule): RedirectResponse
    {
        $discountRule->update($this->validated($request));

        return redirect()->route('discount-rules.index')->with('success', 'Discount rule updated.');
    }

    public function destroy(DiscountRule $discountRule): RedirectResponse
    {
        $discountRule->delete();

        return redirect()->route('discount-rules.index')->with('success', 'Discount rule deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'scope' => ['required', 'in:all,category,product'],
            'scope_id' => ['nullable', 'integer'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $validated['scope_id'] = $validated['scope'] === 'all' ? null : $validated['scope_id'];
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
