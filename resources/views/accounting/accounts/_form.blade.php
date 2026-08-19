@php $a = $account ?? new \App\Models\Account(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" value="{{ old('code', $a->code) }}" required autofocus />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $a->name) }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
            @foreach (['asset', 'liability', 'equity', 'income', 'expense'] as $type)
                <option value="{{ $type }}" @selected(old('type', $a->type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="parent_id" value="Parent Account (optional)" />
        <select id="parent_id" name="parent_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
            <option value="">None</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $a->parent_id) == $parent->id)>{{ $parent->code }} — {{ $parent->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $a->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
