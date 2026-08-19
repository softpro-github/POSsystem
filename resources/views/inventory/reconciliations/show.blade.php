<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reconciliation — {{ $reconciliation->completed_at->format('Y-m-d H:i') }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><div class="text-ink-muted">Store</div><div class="font-medium">{{ $reconciliation->store->name }}</div></div>
            <div><div class="text-ink-muted">By</div><div class="font-medium">{{ $reconciliation->user->name }}</div></div>
            <div><div class="text-ink-muted">Notes</div><div class="font-medium">{{ $reconciliation->notes ?? '—' }}</div></div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4 text-right">Delta</th>
                        <th class="py-3 px-4 text-right">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reconciliation->stockMovements as $movement)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-2 px-4">{{ $movement->product->name }}</td>
                            <td class="py-2 px-4 text-ink-muted">{{ $movement->adjustmentReason?->name ?? '—' }}</td>
                            <td class="py-2 px-4 text-right {{ $movement->quantity >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="py-2 px-4 text-right">{{ $movement->balance_after }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <a href="{{ route('reconciliations.index') }}" class="text-sm text-ink-muted hover:underline">&larr; Back to Reconciliations</a>
    </div>
</x-app-layout>
