<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Categories</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong text-ink-muted rounded-md text-sm hover:bg-surface-hover">Back to Products</a>
                <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Category</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Parent</th>
                            <th class="py-3 px-4 text-right">Products</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $category->name }}</td>
                                <td class="py-3 px-4">{{ $category->parent?->name ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">{{ $category->products_count }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
