<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TaxGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->with('parent', 'taxGroup')->orderBy('name')->paginate(20);

        return view('inventory.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $taxGroups = TaxGroup::where('is_active', true)->orderBy('name')->get();

        return view('inventory.categories.create', compact('categories', 'taxGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $categories = Category::where('id', '!=', $category->id)->orderBy('name')->get();
        $taxGroups = TaxGroup::where('is_active', true)->orderBy('name')->get();

        return view('inventory.categories.edit', compact('category', 'categories', 'taxGroups'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id', 'different:id'],
            'tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ]);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);
        }

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has products or subcategories.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
