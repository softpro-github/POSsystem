<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Offline Sync Log</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <p class="text-sm text-ink-muted">Sales made while the POS was offline, in the order they synced back to the server. Items flagged "Needs Review" had a stock conflict at sync time (oversold, or a serial/IMEI already sold elsewhere) and should be reconciled manually.</p>

        <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Sold Offline At</th>
                        <th class="py-3 px-4">Synced At</th>
                        <th class="py-3 px-4">Cashier</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4">Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($syncedSales as $sale)
                        <tr class="border-b border-border last:border-0 {{ $sale->needs_review ? 'bg-amber-500/10' : '' }}">
                            <td class="py-3 px-4"><a href="{{ route('sales.show', $sale) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $sale->invoice_number }}</a></td>
                            <td class="py-3 px-4">{{ $sale->offline_queued_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">{{ $sale->user->name }}</td>
                            <td class="py-3 px-4 text-right"><x-money :amount="$sale->total_amount" /></td>
                            <td class="py-3 px-4">
                                @if ($sale->needs_review)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-500/10 text-amber-400" title="{{ $sale->review_note }}">Needs Review</span>
                                @else
                                    <span class="text-ink-subtle text-xs">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No offline sales have synced yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $syncedSales->links() }}
    </div>
</x-app-layout>
