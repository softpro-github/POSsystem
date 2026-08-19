<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Transfer #{{ $transfer->id }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><div class="text-ink-muted">From</div><div class="font-medium">{{ $transfer->fromStore->name }}</div></div>
            <div><div class="text-ink-muted">To</div><div class="font-medium">{{ $transfer->toStore->name }}</div></div>
            <div><div class="text-ink-muted">Status</div><div class="font-medium">{{ ucfirst($transfer->status) }}</div></div>
            <div><div class="text-ink-muted">Created By</div><div class="font-medium">{{ $transfer->user->name }}</div></div>
        </div>

        @if ($transfer->notes)
            <div class="bg-surface-raised border border-border rounded-lg p-4 text-sm text-ink-muted">{{ $transfer->notes }}</div>
        @endif

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-4 text-right">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transfer->items as $item)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-2 px-4">{{ $item->product->name }}</td>
                            <td class="py-2 px-4 text-right">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($transfer->status === 'pending')
            <form method="POST" action="{{ route('transfers.receive', $transfer) }}" onsubmit="return confirm('Confirm receipt? This moves stock out of {{ $transfer->fromStore->name }} and into {{ $transfer->toStore->name }}.');">
                @csrf
                <button type="submit" class="px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Confirm Receipt</button>
            </form>
        @endif

        <a href="{{ route('transfers.index') }}" class="text-sm text-ink-muted hover:underline">&larr; Back to Transfers</a>
    </div>
</x-app-layout>
