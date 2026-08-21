<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
            <a href="{{ route('accounting.accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Account</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            <x-flash-messages />

            @foreach ($accounts as $type => $group)
                <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b border-border font-semibold text-ink">{{ ucfirst($type) }}</div>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-hover text-ink-muted border-b border-border">
                            <tr>
                                <th class="py-2 px-4">Code</th>
                                <th class="py-2 px-4">Name</th>
                                <th class="py-2 px-4">Active</th>
                                <th class="py-2 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $account)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2 px-4">{{ $account->code }}</td>
                                    <td class="py-2 px-4">{{ $account->name }}</td>
                                    <td class="py-2 px-4">{{ $account->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="py-2 px-4 text-right space-x-2">
                                        <a href="{{ route('accounting.accounts.edit', $account) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                        <form action="{{ route('accounting.accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Delete this account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
