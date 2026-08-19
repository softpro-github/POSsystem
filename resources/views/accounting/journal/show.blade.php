<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Journal Entry #{{ $journalEntry->id }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><div class="text-ink-muted">Date</div><div class="font-medium">{{ $journalEntry->entry_date->format('Y-m-d') }}</div></div>
            <div><div class="text-ink-muted">Reference</div><div class="font-medium">{{ $journalEntry->referenceLabel() ?? 'Manual' }}</div></div>
            <div><div class="text-ink-muted">Posted By</div><div class="font-medium">{{ $journalEntry->createdBy?->name ?? '—' }}</div></div>
            <div><div class="text-ink-muted">Status</div><div class="font-medium">{{ ucfirst($journalEntry->status) }}</div></div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-4 text-sm text-ink-muted">
            {{ $journalEntry->description }}
        </div>

        @if ($journalEntry->reversedEntry)
            <div class="bg-zinc-500/10 border border-border text-ink-muted text-sm rounded-md px-4 py-3">
                This is a reversal of <a href="{{ route('accounting.journal.show', $journalEntry->reversedEntry) }}" class="text-accent-400 hover:underline">entry #{{ $journalEntry->reversedEntry->id }}</a>.
            </div>
        @endif

        @if ($reversedBy)
            <div class="bg-zinc-500/10 border border-border text-ink-muted text-sm rounded-md px-4 py-3">
                This entry was reversed by <a href="{{ route('accounting.journal.show', $reversedBy) }}" class="text-accent-400 hover:underline">entry #{{ $reversedBy->id }}</a>.
            </div>
        @endif

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Account</th>
                        <th class="py-3 px-4 text-right">Debit</th>
                        <th class="py-3 px-4 text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($journalEntry->lines as $line)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $line->account->code }} — {{ $line->account->name }}</td>
                            <td class="py-3 px-4 text-right">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                            <td class="py-3 px-4 text-right">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-border font-semibold">
                        <td class="py-3 px-4">Total</td>
                        <td class="py-3 px-4 text-right"><x-money :amount="$journalEntry->totalDebit()" /></td>
                        <td class="py-3 px-4 text-right"><x-money :amount="$journalEntry->totalCredit()" /></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @can('manage chart of accounts')
            @if ($journalEntry->status === 'posted')
                <form method="POST" action="{{ route('accounting.journal.reverse', $journalEntry) }}" onsubmit="return confirm('Reverse this journal entry? This posts an equal-and-opposite entry; the original stays untouched.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/30 rounded-md text-sm hover:bg-red-500/20">Reverse Entry</button>
                </form>
            @endif
        @endcan

        <a href="{{ route('accounting.journal.index') }}" class="text-sm text-ink-muted hover:underline">&larr; Back to Journal</a>
    </div>
</x-app-layout>
