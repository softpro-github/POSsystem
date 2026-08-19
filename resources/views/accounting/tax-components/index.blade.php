<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Tax Components</h2>
            <a href="{{ route('tax-components.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Component</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />
        <p class="text-sm text-ink-muted">Individual tax rates (e.g. VAT, State Tax) that get combined into <a href="{{ route('tax-groups.index') }}" class="text-accent-400 hover:underline">Tax Groups</a>.</p>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4 text-right">Rate</th>
                        <th class="py-3 px-4">Active</th>
                        <th class="py-3 px-4 text-right">Used In Groups</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxComponents as $component)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $component->name }}</td>
                            <td class="py-3 px-4 text-right">{{ $component->rate }}%</td>
                            <td class="py-3 px-4">{{ $component->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-3 px-4 text-right">{{ $component->tax_groups_count }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('tax-components.edit', $component) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('tax-components.destroy', $component) }}" method="POST" class="inline" onsubmit="return confirm('Delete this component?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No tax components yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $taxComponents->links() }}
    </div>
</x-app-layout>
