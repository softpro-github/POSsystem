<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Active</div><div class="text-xl font-semibold text-emerald-400">{{ $summary['active'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Expired</div><div class="text-xl font-semibold">{{ $summary['expired'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Voided</div><div class="text-xl font-semibold">{{ $summary['voided'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Expiring in 30 Days</div><div class="text-xl font-semibold text-amber-400">{{ $summary['expiring_soon'] }}</div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">End Date</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warranties as $warranty)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('warranties.show', $warranty) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $warranty->product->name }}</a></td>
                                <td class="py-3 px-4">{{ $warranty->customer->name }}</td>
                                <td class="py-3 px-4">{{ $warranty->end_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">{{ ucfirst($warranty->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No warranties registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
