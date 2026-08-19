<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach (['received', 'diagnosing', 'awaiting_parts', 'in_repair', 'ready_for_pickup', 'completed', 'cancelled'] as $status)
                    <div class="bg-surface-raised border border-border rounded-lg p-4">
                        <div class="text-xs text-ink-muted">{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                        <div class="text-xl font-semibold">{{ $summary[$status] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Ticket #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Technician</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('repair-tickets.show', $ticket) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $ticket->ticket_number }}</a></td>
                                <td class="py-3 px-4">{{ $ticket->customer->name }}</td>
                                <td class="py-3 px-4">{{ $ticket->technician?->name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No repair tickets yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
