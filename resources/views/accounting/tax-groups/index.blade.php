<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Tax Groups</h2>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('tax-components.index') }}" class="text-sm text-ink-muted hover:text-ink hover:underline">Manage Components</a>
                <a href="{{ route('tax-groups.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Group</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Components</th>
                        <th class="py-3 px-4 text-right">Total Rate</th>
                        <th class="py-3 px-4">Default</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxGroups as $group)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $group->name }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $group->components->pluck('name')->implode(', ') ?: '—' }}</td>
                            <td class="py-3 px-4 text-right">{{ $group->totalRate() }}%</td>
                            <td class="py-3 px-4">
                                @if ($group->is_default)
                                    <span class="px-2 py-0.5 rounded text-xs bg-accent-500/10 text-accent-400">Default</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('tax-groups.edit', $group) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('tax-groups.destroy', $group) }}" method="POST" class="inline" onsubmit="return confirm('Delete this tax group?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No tax groups yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $taxGroups->links() }}
    </div>
</x-app-layout>
