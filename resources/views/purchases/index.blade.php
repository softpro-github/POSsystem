<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Purchase Orders</h2>
            <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">New Purchase Order</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Supplier</th>
                            <th class="py-3 px-4">Order Date</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $po)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('purchase-orders.show', $po) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $po->supplier->name }}</a></td>
                                <td class="py-3 px-4">{{ $po->order_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs',
                                        'bg-amber-500/10 text-amber-400' => $po->status === 'pending',
                                        'bg-emerald-500/10 text-emerald-400' => $po->status === 'received',
                                        'bg-surface-hover text-ink-muted' => $po->status === 'cancelled',
                                    ])>{{ ucfirst($po->status) }}</span>
                                </td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$po->total_amount" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No purchase orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $purchaseOrders->links() }}
        </div>
    </div>
</x-app-layout>
