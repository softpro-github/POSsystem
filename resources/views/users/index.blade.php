<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Users</h2>
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add User</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Store</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-border last:border-0 {{ !$user->is_active ? 'opacity-50' : '' }}">
                                <td class="py-3 px-4">{{ $user->name }}</td>
                                <td class="py-3 px-4">{{ $user->email }}</td>
                                <td class="py-3 px-4">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="py-3 px-4 text-ink-muted">{{ $user->store?->name ?? 'All stores' }}</td>
                                <td class="py-3 px-4">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('users.edit', $user) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
