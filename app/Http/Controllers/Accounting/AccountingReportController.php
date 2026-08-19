<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccountingReportController extends Controller
{
    public function trialBalance(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        $rows = $accounts->map(function (Account $account) use ($from, $to) {
            $debit = (float) $account->lines()->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))->sum('debit');
            $credit = (float) $account->lines()->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))->sum('credit');

            return [
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
            ];
        })->filter(fn ($row) => $row['debit'] != 0 || $row['credit'] != 0)->values();

        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');

        return view('accounting.trial-balance', compact('rows', 'totalDebit', 'totalCredit', 'from', 'to'));
    }

    public function generalLedger(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $account = $request->filled('account_id')
            ? $accounts->firstWhere('id', $request->integer('account_id'))
            : $accounts->first();

        $lines = collect();
        $runningBalance = 0;

        if ($account) {
            $entryLines = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))
                ->with('journalEntry')
                ->get()
                ->sortBy(fn ($line) => $line->journalEntry->entry_date)
                ->values();

            $isDebitNormal = in_array($account->type, ['asset', 'expense']);

            $lines = $entryLines->map(function ($line) use (&$runningBalance, $isDebitNormal) {
                $runningBalance += $isDebitNormal ? ($line->debit - $line->credit) : ($line->credit - $line->debit);

                return [
                    'entry' => $line->journalEntry,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'running_balance' => $runningBalance,
                ];
            });
        }

        return view('accounting.general-ledger', compact('accounts', 'account', 'lines', 'from', 'to'));
    }

    public function profitAndLoss(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $income = $this->accountBalances('income', $from, $to);
        $expense = $this->accountBalances('expense', $from, $to);

        $totalIncome = $income->sum('balance');
        $totalExpense = $expense->sum('balance');
        $netIncome = $totalIncome - $totalExpense;

        return view('accounting.profit-and-loss', compact('income', 'expense', 'totalIncome', 'totalExpense', 'netIncome', 'from', 'to'));
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = $request->filled('as_of') ? Carbon::parse($request->string('as_of')) : now();
        $periodStart = Carbon::create(2000, 1, 1);

        $assets = $this->accountBalances('asset', $periodStart, $asOf);
        $liabilities = $this->accountBalances('liability', $periodStart, $asOf);
        $equity = $this->accountBalances('equity', $periodStart, $asOf);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');

        $income = $this->accountBalances('income', $periodStart, $asOf)->sum('balance');
        $expense = $this->accountBalances('expense', $periodStart, $asOf)->sum('balance');
        $currentEarnings = $income - $expense;

        $totalEquity = $equity->sum('balance') + $currentEarnings;

        return view('accounting.balance-sheet', compact(
            'assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'currentEarnings', 'asOf'
        ));
    }

    public function cashFlow(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $cashAccounts = Account::whereIn('code', ['1000', '1010'])->get();

        $lines = JournalEntryLine::whereIn('account_id', $cashAccounts->pluck('id'))
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))
            ->with(['journalEntry', 'account'])
            ->get()
            ->sortBy(fn ($line) => $line->journalEntry->entry_date)
            ->values();

        $inflow = (float) $lines->sum('debit');
        $outflow = (float) $lines->sum('credit');
        $netChange = $inflow - $outflow;

        return view('accounting.cash-flow', compact('lines', 'inflow', 'outflow', 'netChange', 'from', 'to'));
    }

    private function accountBalances(string $type, Carbon $from, Carbon $to)
    {
        return Account::where('is_active', true)->where('type', $type)->orderBy('code')->get()
            ->map(function (Account $account) use ($type, $from, $to) {
                $debit = (float) $account->lines()->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))->sum('debit');
                $credit = (float) $account->lines()->whereHas('journalEntry', fn ($q) => $q->whereBetween('entry_date', [$from->startOfDay(), $to->endOfDay()]))->sum('credit');
                $balance = in_array($type, ['asset', 'expense']) ? $debit - $credit : $credit - $debit;

                return ['account' => $account, 'balance' => $balance];
            })
            ->filter(fn ($row) => $row['balance'] != 0)
            ->values();
    }

    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();

        return [$from, $to];
    }
}
