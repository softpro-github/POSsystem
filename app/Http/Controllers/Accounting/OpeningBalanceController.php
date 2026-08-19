<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpeningBalanceController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function edit(): View
    {
        $locked = $this->isLocked();
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $existingEntry = JournalEntry::where('reference_type', 'opening_balance')->with('lines.account')->first();

        $existingAmounts = [];
        if ($existingEntry) {
            foreach ($existingEntry->lines as $line) {
                $existingAmounts[$line->account_id] = in_array($line->account->type, ['asset', 'expense'])
                    ? (float) $line->debit
                    : (float) $line->credit;
            }
        }

        return view('accounting.opening-balances.edit', compact('accounts', 'locked', 'existingAmounts'));
    }

    public function update(Request $request): RedirectResponse
    {
        if ($this->isLocked()) {
            return back()->with('error', 'Opening balances are locked because transactions already exist.');
        }

        $validated = $request->validate([
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric'],
        ]);

        $accounts = Account::whereIn('id', array_keys($validated['amounts']))->get()->keyBy('id');

        $lines = [];
        foreach ($validated['amounts'] as $accountId => $amount) {
            $amount = (float) $amount;
            if ($amount == 0) {
                continue;
            }

            $account = $accounts->get((int) $accountId);
            if (! $account) {
                continue;
            }

            $lines[] = in_array($account->type, ['asset', 'expense'])
                ? ['account' => $account->code, 'debit' => $amount]
                : ['account' => $account->code, 'credit' => $amount];
        }

        if (empty($lines)) {
            return back()->with('error', 'Enter at least one opening balance.');
        }

        // Opening balances must themselves balance (assets = liabilities + equity),
        // so any shortfall/excess posts to Retained Earnings to force the books level.
        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        $diff = round($totalDebit - $totalCredit, 2);

        if ($diff != 0) {
            $lines[] = $diff > 0
                ? ['account' => '3100', 'credit' => $diff]
                : ['account' => '3100', 'debit' => abs($diff)];
        }

        $this->journalService->postEntry(
            $lines,
            'Opening balances',
            null,
            now(),
            'opening_balance',
        );

        return redirect()->route('accounting.opening-balances.edit')->with('success', 'Opening balances posted.');
    }

    private function isLocked(): bool
    {
        return JournalEntry::where('reference_type', '!=', 'opening_balance')->exists();
    }
}
