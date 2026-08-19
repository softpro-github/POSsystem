<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CashMismatchReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashMismatchReasonController extends Controller
{
    public function index(): View
    {
        $cashMismatchReasons = CashMismatchReason::withCount('shifts')->orderBy('name')->paginate(20);

        return view('settings.cash-mismatch-reasons.index', compact('cashMismatchReasons'));
    }

    public function create(): View
    {
        return view('settings.cash-mismatch-reasons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        CashMismatchReason::create($this->validated($request));

        return redirect()->route('cash-mismatch-reasons.index')->with('success', 'Reason created.');
    }

    public function edit(CashMismatchReason $cashMismatchReason): View
    {
        return view('settings.cash-mismatch-reasons.edit', compact('cashMismatchReason'));
    }

    public function update(Request $request, CashMismatchReason $cashMismatchReason): RedirectResponse
    {
        $cashMismatchReason->update($this->validated($request));

        return redirect()->route('cash-mismatch-reasons.index')->with('success', 'Reason updated.');
    }

    public function destroy(CashMismatchReason $cashMismatchReason): RedirectResponse
    {
        if ($cashMismatchReason->shifts()->exists()) {
            return back()->with('error', 'Cannot delete a reason that has been used.');
        }

        $cashMismatchReason->delete();

        return redirect()->route('cash-mismatch-reasons.index')->with('success', 'Reason deleted.');
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
