<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Customers</h2>
            @can('manage customers')
                <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Customer</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm flex-1 min-w-[200px]">
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Search</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4">Loyalty Points</th>
                            <th class="py-3 px-4 text-right">Purchases</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr class="border-b border-border last:border-0 {{ !$customer->is_active ? 'opacity-50' : '' }}">
                                <td class="py-3 px-4"><a href="{{ route('customers.show', $customer) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $customer->name }}</a></td>
                                <td class="py-3 px-4">{{ $customer->phone }}</td>
                                <td class="py-3 px-4">{{ $customer->loyalty_points }}</td>
                                <td class="py-3 px-4 text-right">{{ $customer->sales_count }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    @can('manage customers')
                                        <a href="{{ route('customers.edit', $customer) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this customer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $customers->links() }}
        </div>
    </div>
</x-app-layout>
