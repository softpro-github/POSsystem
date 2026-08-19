<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdjustmentReasonController extends Controller
{
    public function index(): View
    {
        $adjustmentReasons = AdjustmentReason::withCount('stockMovements')->orderBy('name')->paginate(20);

        return view('settings.adjustment-reasons.index', compact('adjustmentReasons'));
    }

    public function create(): View
    {
        return view('settings.adjustment-reasons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        AdjustmentReason::create($this->validated($request));

        return redirect()->route('adjustment-reasons.index')->with('success', 'Adjustment reason created.');
    }

    public function edit(AdjustmentReason $adjustmentReason): View
    {
        return view('settings.adjustment-reasons.edit', compact('adjustmentReason'));
    }

    public function update(Request $request, AdjustmentReason $adjustmentReason): RedirectResponse
    {
        $adjustmentReason->update($this->validated($request));

        return redirect()->route('adjustment-reasons.index')->with('success', 'Adjustment reason updated.');
    }

    public function destroy(AdjustmentReason $adjustmentReason): RedirectResponse
    {
        if ($adjustmentReason->stockMovements()->exists()) {
            return back()->with('error', 'Cannot delete an adjustment reason that has been used.');
        }

        $adjustmentReason->delete();

        return redirect()->route('adjustment-reasons.index')->with('success', 'Adjustment reason deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
