<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Discount Rules</h2>
            <a href="{{ route('discount-rules.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Rule</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4 text-right">Value</th>
                            <th class="py-3 px-4">Scope</th>
                            <th class="py-3 px-4">Active</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($discountRules as $rule)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $rule->name }}</td>
                                <td class="py-3 px-4">{{ ucfirst($rule->type) }}</td>
                                <td class="py-3 px-4 text-right">{{ $rule->type === 'percentage' ? $rule->value.'%' : number_format($rule->value, 2) }}</td>
                                <td class="py-3 px-4">{{ ucfirst($rule->scope) }}</td>
                                <td class="py-3 px-4">{{ $rule->is_active ? 'Yes' : 'No' }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('discount-rules.edit', $rule) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                    <form action="{{ route('discount-rules.destroy', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Delete this rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No discount rules yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $discountRules->links() }}
        </div>
    </div>
</x-app-layout>
