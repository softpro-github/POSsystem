<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Suppliers</h2>
            <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Supplier</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm flex-1 min-w-[200px]">
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Search</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Contact Person</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4 text-right">Purchase Orders</th>
                            <th class="py-3 px-4 text-right">Balance Owed</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4"><a href="{{ route('suppliers.show', $supplier) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $supplier->name }}</a></td>
                                <td class="py-3 px-4">{{ $supplier->contact_person ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $supplier->phone ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">{{ $supplier->purchase_orders_count }}</td>
                                <td class="py-3 px-4 text-right {{ $supplier->balance > 0 ? 'text-amber-400' : '' }}"><x-money :amount="$supplier->balance" /></td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Delete this supplier?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No suppliers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $suppliers->links() }}
        </div>
    </div>
</x-app-layout>
