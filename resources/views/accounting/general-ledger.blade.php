<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <label class="text-sm text-ink-muted">Account
                    <select name="account_id" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1" onchange="this.form.submit()">
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected($account && $account->id === $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-ink-muted">From
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                </label>
                <label class="text-sm text-ink-muted">To
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                </label>
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Apply</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4 text-right">Debit</th>
                            <th class="py-3 px-4 text-right">Credit</th>
                            <th class="py-3 px-4 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lines as $line)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $line['entry']->entry_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('accounting.journal.show', $line['entry']) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $line['entry']->description }}</a>
                                </td>
                                <td class="py-3 px-4 text-right">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '' }}</td>
                                <td class="py-3 px-4 text-right">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '' }}</td>
                                <td class="py-3 px-4 text-right font-medium"><x-money :amount="$line['running_balance']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No activity for this account in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
