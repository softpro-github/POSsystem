<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Single source of truth for double-entry postings — every journal entry
 * (automatic or manual) goes through postEntry(), which enforces debits =
 * credits and that the entry date isn't in a locked fiscal period. History
 * is never edited/deleted after posting; corrections go through reverseEntry().
 */
class JournalService
{
    /**
     * @param  array<int, array{account: string, debit?: float, credit?: float}>  $lines
     */
    public function postEntry(
        array $lines,
        string $description,
        ?Model $reference = null,
        ?Carbon $date = null,
        ?string $referenceTypeOverride = null,
    ): JournalEntry {
        $date ??= now();

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw ValidationException::withMessages([
                'lines' => "Journal entry does not balance: debits {$totalDebit} vs credits {$totalCredit}.",
            ]);
        }

        if ($this->isPeriodLocked($date)) {
            throw ValidationException::withMessages([
                'entry_date' => 'Cannot post to a locked fiscal period ('.$date->format('Y-m').').',
            ]);
        }

        return DB::transaction(function () use ($lines, $description, $reference, $date, $referenceTypeOverride) {
            $entry = JournalEntry::create([
                'entry_date' => $date,
                'reference_type' => $referenceTypeOverride ?? $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'description' => $description,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                if (empty($line['debit']) && empty($line['credit'])) {
                    continue;
                }

                $account = $this->resolveAccount($line['account']);

                $entry->lines()->create([
                    'account_id' => $account->id,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $entry;
        });
    }

    public function reverseEntry(JournalEntry $entry, ?string $description = null): JournalEntry
    {
        if ($entry->status === 'reversed') {
            throw ValidationException::withMessages(['status' => 'This entry has already been reversed.']);
        }

        $entry->load('lines.account');

        $lines = $entry->lines->map(fn ($line) => [
            'account' => $line->account->code,
            'debit' => (float) $line->credit,
            'credit' => (float) $line->debit,
        ])->all();

        return DB::transaction(function () use ($entry, $lines, $description) {
            $reversal = $this->postEntry(
                $lines,
                $description ?? 'Reversal of entry #'.$entry->id.' — '.$entry->description,
                null,
                now(),
                $entry->reference_type,
            );

            $reversal->update(['reversed_entry_id' => $entry->id]);
            $entry->update(['status' => 'reversed']);

            return $reversal;
        });
    }

    public function isPeriodLocked(Carbon $date): bool
    {
        return FiscalPeriod::where('year', $date->year)
            ->where('month', $date->month)
            ->where('is_locked', true)
            ->exists();
    }

    private function resolveAccount(string $code): Account
    {
        $account = Account::where('code', $code)->first();

        if (! $account) {
            throw ValidationException::withMessages(['account' => "Unknown account code: {$code}."]);
        }

        return $account;
    }
}
