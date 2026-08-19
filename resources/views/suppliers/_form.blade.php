@php $s = $supplier ?? new \App\Models\Supplier(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $s->name) }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" value="{{ old('contact_person', $s->contact_person) }}" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone', $s->phone) }}" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $s->email) }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" value="{{ old('address', $s->address) }}" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
</div>
