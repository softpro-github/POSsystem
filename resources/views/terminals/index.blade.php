<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Terminals</h2>
            <a href="{{ route('terminals.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Terminal</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <p class="text-sm text-ink-muted">Optional — only needed if a store has more than one physical till/register. Cashiers pick a terminal when opening a shift, but only if the store has at least one active terminal configured.</p>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Store</th>
                        <th class="py-3 px-4">Active</th>
                        <th class="py-3 px-4 text-right">Shifts</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terminals as $terminal)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $terminal->name }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $terminal->store->name }}</td>
                            <td class="py-3 px-4">{{ $terminal->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-3 px-4 text-right">{{ $terminal->shifts_count }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('terminals.edit', $terminal) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('terminals.destroy', $terminal) }}" method="POST" class="inline" onsubmit="return confirm('Delete this terminal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No terminals configured yet — each store just uses a single till by default.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $terminals->links() }}
    </div>
</x-app-layout>
