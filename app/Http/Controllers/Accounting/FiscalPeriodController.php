<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntryLine;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FiscalPeriodController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function index(): View
    {
        $existing = FiscalPeriod::orderBy('year')->orderBy('month')->get()->keyBy(fn ($p) => $p->year.'-'.$p->month);

        $earliestEntryYear = (int) (\App\Models\JournalEntry::min('entry_date')
            ? Carbon::parse(\App\Models\JournalEntry::min('entry_date'))->year
            : now()->year);

        $months = collect();
        for ($year = now()->year; $year >= $earliestEntryYear; $year--) {
            for ($month = 12; $month >= 1; $month--) {
                if ($year === now()->year && $month > now()->month) {
                    continue;
                }

                $key = $year.'-'.$month;
                $months->push([
                    'year' => $year,
                    'month' => $month,
                    'label' => Carbon::create($year, $month, 1)->format('F Y'),
                    'period' => $existing->get($key),
                ]);
            }
        }

        $years = $months->pluck('year')->unique()->values();

        return view('accounting.fiscal-periods.index', compact('months', 'years'));
    }

    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $period = FiscalPeriod::firstOrNew(['year' => $validated['year'], 'month' => $validated['month']]);
        $period->is_locked = ! $period->is_locked;
        $period->closed_at = $period->is_locked ? now() : null;
        $period->save();

        return redirect()->route('accounting.fiscal-periods.index')
            ->with('success', 'Period '.($period->is_locked ? 'locked' : 'unlocked').'.');
    }

    public function closeYear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
        ]);

        $year = $validated['year'];
        $from = Carbon::create($year, 1, 1)->startOfDay();
        $to = Carbon::create($year, 12, 31)->endOfDay();

        $lines = [];
        $netIncome = 0;

        foreach (['income', 'expense'] as $type) {
            $accounts = Account::where('type', $type)->where('is_active', true)->get();

            foreach ($accounts as $account) {
                $debit = (float) JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from, $to]))
                    ->sum('debit');
                $credit = (float) JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from, $to]))
                    ->sum('credit');

                $balance = $type === 'income' ? $credit - $debit : $debit - $credit;

                if ($balance == 0) {
                    continue;
                }

                if ($type === 'income') {
                    $lines[] = ['account' => $account->code, 'debit' => $balance, 'credit' => 0];
                    $netIncome += $balance;
                } else {
                    $lines[] = ['account' => $account->code, 'debit' => 0, 'credit' => $balance];
                    $netIncome -= $balance;
                }
            }
        }

        if (empty($lines)) {
            return back()->with('error', "No income or expense activity found for {$year}.");
        }

        $lines[] = $netIncome > 0
            ? ['account' => '3100', 'debit' => 0, 'credit' => $netIncome]
            : ['account' => '3100', 'debit' => abs($netIncome), 'credit' => 0];

        $this->journalService->postEntry(
            $lines,
            "Year-end close {$year}: net income rolled to Retained Earnings",
            null,
            $to,
        );

        for ($month = 1; $month <= 12; $month++) {
            $period = FiscalPeriod::firstOrNew(['year' => $year, 'month' => $month]);
            $period->is_locked = true;
            $period->closed_at = now();
            $period->save();
        }

        return redirect()->route('accounting.fiscal-periods.index')->with('success', "Year {$year} closed and locked.");
    }
}
