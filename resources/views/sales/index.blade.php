<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Sales History</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <input type="date" name="from" value="{{ request('from') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Filter</button>
                @if (request()->hasAny(['search', 'from', 'to']))
                    <a href="{{ route('sales.index') }}" class="text-sm text-ink-muted hover:underline">Clear</a>
                @endif
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Cashier</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('sales.show', $sale) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $sale->invoice_number }}</a></td>
                                <td class="py-3 px-4">{{ $sale->sold_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-3 px-4">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="py-3 px-4">{{ $sale->user->name }}</td>
                                <td class="py-3 px-4">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs',
                                        'bg-emerald-500/10 text-emerald-400' => $sale->status === 'completed',
                                        'bg-red-500/10 text-red-400' => $sale->status === 'voided',
                                        'bg-amber-500/10 text-amber-400' => $sale->status === 'held',
                                        'bg-surface-hover text-ink-muted' => $sale->status === 'refunded',
                                    ])>{{ ucfirst($sale->status) }}</span>
                                </td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$sale->total_amount" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No sales found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sales->links() }}
        </div>
    </div>
</x-app-layout>
