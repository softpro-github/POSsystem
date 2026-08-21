<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Repair Ticket {{ $repairTicket->ticket_number }}</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('repair-tickets.edit', $repairTicket) }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline self-center">Edit</a>
                <form action="{{ route('repair-tickets.destroy', $repairTicket) }}" method="POST" onsubmit="return confirm('Delete this ticket?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div><div class="text-ink-muted">Customer</div><div class="font-medium">{{ $repairTicket->customer->name }}</div></div>
                <div><div class="text-ink-muted">Device</div><div class="font-medium">{{ $repairTicket->device_type }} {{ $repairTicket->device_brand }} {{ $repairTicket->device_model }}</div></div>
                <div><div class="text-ink-muted">Technician</div><div class="font-medium">{{ $repairTicket->technician?->name ?? 'Unassigned' }}</div></div>
                <div><div class="text-ink-muted">Received</div><div class="font-medium">{{ $repairTicket->received_date->format('Y-m-d') }}</div></div>
                <div><div class="text-ink-muted">Estimated Cost</div><div class="font-medium"><x-money :amount="$repairTicket->estimated_cost ?? 0" /></div></div>
                <div><div class="text-ink-muted">Final Cost</div><div class="font-medium"><x-money :amount="$repairTicket->final_cost ?? 0" /></div></div>
                <div class="col-span-2 sm:col-span-3"><div class="text-ink-muted">Issue</div><div class="font-medium">{{ $repairTicket->issue_description }}</div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-3">Update Status</h3>
                <form action="{{ route('repair-tickets.status', $repairTicket) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                        @foreach (['received', 'diagnosing', 'awaiting_parts', 'in_repair', 'ready_for_pickup', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($repairTicket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" placeholder="Note (optional)" class="text-sm bg-surface-hover border-border-strong text-ink rounded-md flex-1 min-w-[150px]">
                    <x-primary-button>Update</x-primary-button>
                </form>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-3">Status History</h3>
                <div class="space-y-3">
                    @foreach ($repairTicket->statusLogs as $log)
                        <div class="text-sm border-b border-border last:border-0 pb-2">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</span>
                                <span class="text-ink-subtle">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            @if ($log->note)
                                <p class="text-ink-muted">{{ $log->note }}</p>
                            @endif
                            <p class="text-xs text-ink-subtle">by {{ $log->changedBy->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
