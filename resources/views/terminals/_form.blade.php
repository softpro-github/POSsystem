@php $t = $terminal ?? new \App\Models\Terminal(); @endphp

<div>
    <x-input-label for="store_id" value="Store" />
    <select id="store_id" name="store_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
        <option value="">Select store</option>
        @foreach ($stores as $store)
            <option value="{{ $store->id }}" @selected(old('store_id', $t->store_id) == $store->id)>{{ $store->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('store_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $t->name) }}" placeholder="e.g. Register 1" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $t->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
