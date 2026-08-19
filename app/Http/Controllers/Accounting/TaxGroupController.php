<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\TaxComponent;
use App\Models\TaxGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaxGroupController extends Controller
{
    public function index(): View
    {
        $taxGroups = TaxGroup::with('components')->withCount(['categories', 'products'])->orderBy('name')->paginate(20);

        return view('accounting.tax-groups.index', compact('taxGroups'));
    }

    public function create(): View
    {
        $taxComponents = TaxComponent::where('is_active', true)->orderBy('name')->get();

        return view('accounting.tax-groups.create', compact('taxComponents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated, $request) {
            if ($validated['is_default']) {
                TaxGroup::where('is_default', true)->update(['is_default' => false]);
            }

            $group = TaxGroup::create($validated);
            $group->components()->sync($request->input('component_ids', []));
        });

        return redirect()->route('tax-groups.index')->with('success', 'Tax group created.');
    }

    public function edit(TaxGroup $taxGroup): View
    {
        $taxComponents = TaxComponent::where('is_active', true)->orderBy('name')->get();
        $taxGroup->load('components');

        return view('accounting.tax-groups.edit', compact('taxGroup', 'taxComponents'));
    }

    public function update(Request $request, TaxGroup $taxGroup): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated, $request, $taxGroup) {
            if ($validated['is_default'] && ! $taxGroup->is_default) {
                TaxGroup::where('is_default', true)->update(['is_default' => false]);
            }

            $taxGroup->update($validated);
            $taxGroup->components()->sync($request->input('component_ids', []));
        });

        return redirect()->route('tax-groups.index')->with('success', 'Tax group updated.');
    }

    public function destroy(TaxGroup $taxGroup): RedirectResponse
    {
        if ($taxGroup->is_default) {
            return back()->with('error', 'Cannot delete the default tax group — set another group as default first.');
        }

        if ($taxGroup->categories()->exists() || $taxGroup->products()->exists()) {
            return back()->with('error', 'Cannot delete a tax group that is assigned to categories or products.');
        }

        $taxGroup->delete();

        return redirect()->route('tax-groups.index')->with('success', 'Tax group deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'component_ids' => ['nullable', 'array'],
            'component_ids.*' => ['exists:tax_components,id'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        unset($validated['component_ids']);

        return $validated;
    }
}
