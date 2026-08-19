@php $s = $store ?? new \App\Models\Store(); @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $s->name) }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="address" value="Address (optional)" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" value="{{ old('address', $s->address) }}" />
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" value="Phone (optional)" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $s->phone) }}" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $s->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
