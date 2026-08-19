<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::withCount('products')->orderBy('name')->paginate(20);

        return view('inventory.units.index', compact('units'));
    }

    public function create(): View
    {
        return view('inventory.units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit): View
    {
        return view('inventory.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validated($request, $unit));

        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return back()->with('error', 'Cannot delete a unit that has products.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }

    private function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'.($unit ? ",{$unit->id}" : '')],
            'abbreviation' => ['nullable', 'string', 'max:20'],
        ]);
    }
}
