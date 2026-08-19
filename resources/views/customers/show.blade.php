<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">{{ $customer->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-ink-muted">Phone</div><div class="font-medium">{{ $customer->phone }}</div></div>
                <div><div class="text-ink-muted">Email</div><div class="font-medium">{{ $customer->email ?? '—' }}</div></div>
                <div><div class="text-ink-muted">Address</div><div class="font-medium">{{ $customer->address ?? '—' }}</div></div>
                <div><div class="text-ink-muted">Loyalty Points</div><div class="font-medium">{{ $customer->loyalty_points }}</div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-4">Purchase History</h3>
                @if ($sales->isEmpty())
                    <p class="text-sm text-ink-muted">No purchases yet.</p>
                @else
                    <table class="w-full text-sm text-left">
                        <thead class="text-ink-muted border-b">
                            <tr><th class="py-2">Invoice</th><th class="py-2">Date</th><th class="py-2 text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2"><a href="{{ route('sales.show', $sale) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $sale->invoice_number }}</a></td>
                                    <td class="py-2">{{ $sale->sold_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-2 text-right"><x-money :amount="$sale->total_amount" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $sales->links() }}
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <h3 class="font-semibold text-ink mb-4">Warranties</h3>
                    @if ($warranties->isEmpty())
                        <p class="text-sm text-ink-muted">None.</p>
                    @else
                        @foreach ($warranties as $warranty)
                            <div class="border-b border-border last:border-0 py-2 text-sm flex justify-between">
                                <span>{{ $warranty->product->name }}</span>
                                <span class="text-ink-muted">until {{ $warranty->end_date->format('Y-m-d') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <h3 class="font-semibold text-ink mb-4">Repair Tickets</h3>
                    @if ($repairTickets->isEmpty())
                        <p class="text-sm text-ink-muted">None.</p>
                    @else
                        @foreach ($repairTickets as $ticket)
                            <div class="border-b border-border last:border-0 py-2 text-sm flex justify-between">
                                <a href="{{ route('repair-tickets.show', $ticket) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $ticket->ticket_number }}</a>
                                <span class="text-ink-muted">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
