<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Record Expense</h2>
    </x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6">
            @if ($expenseCategories->isEmpty())
                <p class="text-sm text-ink-muted">No expense categories exist yet. <a href="{{ route('expense-categories.create') }}" class="text-accent-400 hover:underline">Create one first</a>.</p>
            @else
                <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="expense_category_id" value="Category" />
                        <select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select category</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}" @selected(old('expense_category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" value="Amount" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" value="{{ old('amount') }}" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="expense_date" value="Date" />
                        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="paid_via" value="Paid Via" />
                        <select id="paid_via" name="paid_via" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="cash" @selected(old('paid_via') === 'cash')>Cash</option>
                            <option value="bank" @selected(old('paid_via') === 'bank')>Bank</option>
                        </select>
                        <x-input-error :messages="$errors->get('paid_via')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description (optional)" />
                        <textarea id="description" name="description" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Save</x-primary-button>
                        <a href="{{ route('expenses.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
