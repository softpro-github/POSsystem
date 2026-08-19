<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Units</h2>
            <div class="flex gap-3">
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong text-ink-muted rounded-md text-sm hover:bg-surface-hover">Back to Products</a>
                <a href="{{ route('units.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Unit</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Abbreviation</th>
                        <th class="py-3 px-4 text-right">Products</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $unit->name }}</td>
                            <td class="py-3 px-4">{{ $unit->abbreviation ?? '—' }}</td>
                            <td class="py-3 px-4 text-right">{{ $unit->products_count }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('units.edit', $unit) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm('Delete this unit?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No units yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $units->links() }}
    </div>
</x-app-layout>
