<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Warranties</h2>
            <a href="{{ route('warranties.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Register Warranty</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Start</th>
                            <th class="py-3 px-4">End</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warranties as $warranty)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('warranties.show', $warranty) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $warranty->product->name }}</a></td>
                                <td class="py-3 px-4">{{ $warranty->customer->name }}</td>
                                <td class="py-3 px-4">{{ $warranty->start_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">{{ $warranty->end_date->format('Y-m-d') }}</td>
                                <td class="py-3 px-4">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs',
                                        'bg-emerald-500/10 text-emerald-400' => $warranty->status === 'active',
                                        'bg-surface-hover text-ink-muted' => $warranty->status === 'expired',
                                        'bg-red-500/10 text-red-400' => $warranty->status === 'voided',
                                    ])>{{ ucfirst($warranty->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No warranties registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $warranties->links() }}
        </div>
    </div>
</x-app-layout>
