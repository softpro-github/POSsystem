<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            @include('reports._date_filter')

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Cash In</div><div class="text-xl font-semibold text-emerald-400"><x-money :amount="$inflow" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Cash Out</div><div class="text-xl font-semibold text-red-400"><x-money :amount="$outflow" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Net Change</div><div class="text-xl font-semibold"><x-money :amount="$netChange" /></div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Account</th>
                            <th class="py-3 px-4 text-right">In</th>
                            <th class="py-3 px-4 text-right">Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lines as $line)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $line->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('accounting.journal.show', $line->journalEntry) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $line->journalEntry->description }}</a>
                                </td>
                                <td class="py-3 px-4">{{ $line->account->name }}</td>
                                <td class="py-3 px-4 text-right">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                                <td class="py-3 px-4 text-right">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No cash movement in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
