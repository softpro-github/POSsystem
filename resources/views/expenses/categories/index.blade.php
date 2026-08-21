<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Expense Categories</h2>
            <a href="{{ route('expense-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Category</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">GL Account</th>
                        <th class="py-3 px-4">Active</th>
                        <th class="py-3 px-4 text-right">Used</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenseCategories as $category)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $category->name }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $category->account->code }} — {{ $category->account->name }}</td>
                            <td class="py-3 px-4">{{ $category->is_active ? 'Yes' : 'No' }}</td>
                            <td class="py-3 px-4 text-right">{{ $category->expenses_count }}</td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('expense-categories.edit', $category) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No expense categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $expenseCategories->links() }}
    </div>
</x-app-layout>
