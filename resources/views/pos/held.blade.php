<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Held Orders</h2>
            <a href="{{ route('pos.index') }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline self-center">Back to POS</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Held At</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Cashier</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($heldOrders as $sale)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4">{{ $sale->user->name }}</td>
                            <td class="py-3 px-4">{{ $sale->items->count() }} item(s)</td>
                            <td class="py-3 px-4 text-right"><x-money :amount="$sale->total_amount" /></td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('pos.index', ['resume' => $sale->id]) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Resume</a>
                                <form action="{{ route('pos.held.destroy', $sale) }}" method="POST" class="inline" onsubmit="return confirm('Discard this held order?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Discard</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No held orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
