<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\TaxComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxComponentController extends Controller
{
    public function index(): View
    {
        $taxComponents = TaxComponent::withCount('taxGroups')->orderBy('name')->paginate(20);

        return view('accounting.tax-components.index', compact('taxComponents'));
    }

    public function create(): View
    {
        return view('accounting.tax-components.create');
    }

    public function store(Request $request): RedirectResponse
    {
        TaxComponent::create($this->validated($request));

        return redirect()->route('tax-components.index')->with('success', 'Tax component created.');
    }

    public function edit(TaxComponent $taxComponent): View
    {
        return view('accounting.tax-components.edit', compact('taxComponent'));
    }

    public function update(Request $request, TaxComponent $taxComponent): RedirectResponse
    {
        $taxComponent->update($this->validated($request));

        return redirect()->route('tax-components.index')->with('success', 'Tax component updated.');
    }

    public function destroy(TaxComponent $taxComponent): RedirectResponse
    {
        if ($taxComponent->taxGroups()->exists()) {
            return back()->with('error', 'Cannot delete a tax component that belongs to a tax group.');
        }

        $taxComponent->delete();

        return redirect()->route('tax-components.index')->with('success', 'Tax component deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
