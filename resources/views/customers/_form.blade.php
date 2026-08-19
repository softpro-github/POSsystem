@php $c = $customer ?? new \App\Models\Customer(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $c->name) }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $c->phone) }}" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email (optional)" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $c->email) }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="address" value="Address (optional)" />
        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" value="{{ old('address', $c->address) }}" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    @if ($customer)
        <div class="flex items-center gap-2 mt-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $c->is_active)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
            <x-input-label for="is_active" value="Active" class="!mb-0" />
        </div>
    @endif
</div>
