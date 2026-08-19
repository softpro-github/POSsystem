<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Cash Mismatch Reasons</h2>
            <a href="{{ route('cash-mismatch-reasons.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Reason</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Active</th>
                        <th class="py-3 px-4 text-right">Used</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cashMismatchReasons as $reason)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $reason->name }}</td>
                            <td class="py-3 px-4">{{ $reason->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-3 px-4 text-right">{{ $reason->shifts_count }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('cash-mismatch-reasons.edit', $reason) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('cash-mismatch-reasons.destroy', $reason) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reason?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No reasons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $cashMismatchReasons->links() }}
    </div>
</x-app-layout>
