@php $c = $taxComponent ?? new \App\Models\TaxComponent(); @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $c->name) }}" placeholder="e.g. VAT" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="rate" value="Rate (%)" />
    <x-text-input id="rate" name="rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" value="{{ old('rate', $c->rate) }}" required />
    <x-input-error :messages="$errors->get('rate')" class="mt-2" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $c->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
