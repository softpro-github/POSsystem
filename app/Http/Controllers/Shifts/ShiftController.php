<?php

namespace App\Http\Controllers\Shifts;

use App\Http\Controllers\Controller;
use App\Models\CashMismatchReason;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\Terminal;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function openForm(): View
    {
        $store = current_store();
        $terminals = $store
            ? Terminal::where('store_id', $store->id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('shifts.open', compact('terminals'));
    }

    public function open(Request $request): RedirectResponse
    {
        if ($this->openShiftFor($request->user())) {
            return redirect()->route('shifts.current');
        }

        $store = current_store();
        $hasTerminals = $store && Terminal::where('store_id', $store->id)->where('is_active', true)->exists();

        $validated = $request->validate([
            'opening_float' => ['required', 'numeric', 'min:0'],
            'terminal_id' => [$hasTerminals ? 'required' : 'nullable', 'exists:terminals,id'],
        ]);

        Shift::create([
            'store_id' => $store?->id,
            'terminal_id' => $validated['terminal_id'] ?? null,
            'user_id' => $request->user()->id,
            'opening_float' => $validated['opening_float'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return redirect()->route('pos.index')->with('success', 'Shift started.');
    }

    public function current(Request $request): View|RedirectResponse
    {
        $shift = $this->openShiftFor($request->user());

        if (! $shift) {
            return redirect()->route('shifts.open-form');
        }

        $cashMismatchReasons = CashMismatchReason::where('is_active', true)->orderBy('name')->get();
        $tolerance = (float) Setting::get('cash_variance_tolerance', 0);
        $suppliersOwed = Supplier::where('balance', '>', 0)->orderBy('name')->get();

        return view('shifts.current', compact('shift', 'cashMismatchReasons', 'tolerance', 'suppliersOwed'));
    }

    public function close(Request $request): RedirectResponse
    {
        $shift = $this->openShiftFor($request->user());

        if (! $shift) {
            return redirect()->route('shifts.open-form');
        }

        $validated = $request->validate([
            'closing_count' => ['required', 'numeric', 'min:0'],
            'cash_mismatch_reason_id' => ['nullable', 'exists:cash_mismatch_reasons,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $expectedCash = $shift->computedExpectedCash();
        $variance = round($validated['closing_count'] - $expectedCash, 2);
        $tolerance = (float) Setting::get('cash_variance_tolerance', 0);

        if (abs($variance) > $tolerance && empty($validated['cash_mismatch_reason_id'])) {
            throw ValidationException::withMessages([
                'cash_mismatch_reason_id' => 'A mismatch reason is required — variance of '.number_format($variance, 2).' exceeds the allowed tolerance.',
            ]);
        }

        DB::transaction(function () use ($shift, $validated, $expectedCash, $variance) {
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_count' => $validated['closing_count'],
                'expected_cash' => $expectedCash,
                'variance' => $variance,
                'cash_mismatch_reason_id' => $validated['cash_mismatch_reason_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($variance !== 0.0) {
                $amount = abs($variance);
                $lines = $variance > 0
                    ? [
                        ['account' => '1000', 'debit' => $amount, 'credit' => 0],
                        ['account' => '5400', 'debit' => 0, 'credit' => $amount],
                    ]
                    : [
                        ['account' => '5400', 'debit' => $amount, 'credit' => 0],
                        ['account' => '1000', 'debit' => 0, 'credit' => $amount],
                    ];

                $this->journalService->postEntry(
                    lines: $lines,
                    description: 'Cash variance: shift #'.$shift->id,
                    reference: $shift,
                );
            }
        });

        return redirect()->route('shifts.show', $shift)->with('success', 'Shift closed.');
    }

    public function index(Request $request): View
    {
        $shifts = Shift::with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->latest('opened_at')
            ->paginate(20)
            ->withQueryString();

        $summaryCounts = [
            'total' => Shift::count(),
            'open' => Shift::where('status', 'open')->count(),
            'with_variance' => Shift::where('status', 'closed')->where('variance', '!=', 0)->count(),
        ];

        $myOpenShift = $this->openShiftFor($request->user());

        return view('shifts.index', compact('shifts', 'summaryCounts', 'myOpenShift'));
    }

    public function show(Shift $shift): View
    {
        $shift->load('user', 'store', 'terminal', 'cashMismatchReason', 'sales.payments');

        return view('shifts.show', compact('shift'));
    }

    private function openShiftFor($user): ?Shift
    {
        return Shift::where('user_id', $user->id)->where('status', 'open')->first();
    }
}
