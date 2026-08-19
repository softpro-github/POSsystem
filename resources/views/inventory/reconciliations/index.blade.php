<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Stock Reconciliations</h2>
            <a href="{{ route('reconciliations.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">New Reconciliation</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Store</th>
                        <th class="py-3 px-4">By</th>
                        <th class="py-3 px-4">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reconciliations as $reconciliation)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">
                                <a href="{{ route('reconciliations.show', $reconciliation) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $reconciliation->completed_at->format('Y-m-d H:i') }}</a>
                            </td>
                            <td class="py-3 px-4">{{ $reconciliation->store->name }}</td>
                            <td class="py-3 px-4">{{ $reconciliation->user->name }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $reconciliation->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No reconciliations recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reconciliations->links() }}
    </div>
</x-app-layout>
