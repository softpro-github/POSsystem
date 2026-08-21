<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Repair Tickets</h2>
            <a href="{{ route('repair-tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">New Repair Ticket</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket number" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <select name="status" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                    <option value="">All statuses</option>
                    @foreach (['received', 'diagnosing', 'awaiting_parts', 'in_repair', 'ready_for_pickup', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Filter</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Ticket #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Device</th>
                            <th class="py-3 px-4">Technician</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($repairTickets as $ticket)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('repair-tickets.show', $ticket) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $ticket->ticket_number }}</a></td>
                                <td class="py-3 px-4">{{ $ticket->customer->name }}</td>
                                <td class="py-3 px-4">{{ $ticket->device_type }} {{ $ticket->device_brand }}</td>
                                <td class="py-3 px-4">{{ $ticket->technician?->name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No repair tickets yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $repairTickets->links() }}
        </div>
    </div>
</x-app-layout>
