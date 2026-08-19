<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')
            @include('reports._date_filter')

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Transactions</div><div class="text-xl font-semibold">{{ $summary['count'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Subtotal</div><div class="text-xl font-semibold"><x-money :amount="$summary['subtotal']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Discounts</div><div class="text-xl font-semibold"><x-money :amount="$summary['discount_amount']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Total Revenue</div><div class="text-xl font-semibold"><x-money :amount="$summary['total_amount']" /></div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Invoice</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Cashier</th>
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
                                <td class="py-3 px-4 text-right"><x-money :amount="$sale->total_amount" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
