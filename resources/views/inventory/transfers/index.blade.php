<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Stock Transfers</h2>
            <a href="{{ route('transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">New Transfer</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">From</th>
                        <th class="py-3 px-4">To</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">
                                <a href="{{ route('transfers.show', $transfer) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $transfer->created_at->format('Y-m-d H:i') }}</a>
                            </td>
                            <td class="py-3 px-4">{{ $transfer->fromStore->name }}</td>
                            <td class="py-3 px-4">{{ $transfer->toStore->name }}</td>
                            <td class="py-3 px-4">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs',
                                    'bg-amber-500/10 text-amber-400' => $transfer->status === 'pending',
                                    'bg-emerald-500/10 text-emerald-400' => $transfer->status === 'received',
                                ])>{{ ucfirst($transfer->status) }}</span>
                            </td>
                            <td class="py-3 px-4 text-ink-muted">{{ $transfer->user->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No transfers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transfers->links() }}
    </div>
</x-app-layout>
