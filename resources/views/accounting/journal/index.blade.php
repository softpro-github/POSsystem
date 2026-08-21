<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
            @can('manage chart of accounts')
                <a href="{{ route('accounting.journal.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">New Manual Entry</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <label class="text-sm text-ink-muted">From
                    <input type="date" name="from" value="{{ request('from') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                </label>
                <label class="text-sm text-ink-muted">To
                    <input type="date" name="to" value="{{ request('to') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                </label>
                <label class="text-sm text-ink-muted">Status
                    <select name="status" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                        <option value="">All</option>
                        <option value="posted" @selected(request('status') === 'posted')>Posted</option>
                        <option value="reversed" @selected(request('status') === 'reversed')>Reversed</option>
                    </select>
                </label>
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Filter</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Reference</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $entry->entry_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('accounting.journal.show', $entry) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $entry->description }}</a>
                                </td>
                                <td class="py-3 px-4 text-ink-muted">{{ $entry->referenceLabel() ?? 'Manual' }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$entry->totalDebit()" /></td>
                                <td class="py-3 px-4">
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs',
                                        'bg-emerald-500/10 text-emerald-400' => $entry->status === 'posted',
                                        'bg-zinc-500/10 text-ink-muted' => $entry->status === 'reversed',
                                    ])>{{ ucfirst($entry->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No journal entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $entries->links() }}
        </div>
    </div>
</x-app-layout>
