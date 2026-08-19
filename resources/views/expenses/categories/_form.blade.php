@php $c = $expenseCategory ?? new \App\Models\ExpenseCategory(); @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $c->name) }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="account_id" value="Posts to GL Account" />
    <select id="account_id" name="account_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
        <option value="">Select account</option>
        @foreach ($accounts as $account)
            <option value="{{ $account->id }}" @selected(old('account_id', $c->account_id) == $account->id)>{{ $account->code }} — {{ $account->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $c->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
