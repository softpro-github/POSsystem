@php $g = $taxGroup ?? new \App\Models\TaxGroup(); $selectedIds = old('component_ids', $g->components->pluck('id')->all() ?? []); @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $g->name) }}" placeholder="e.g. Standard" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label value="Components" />
    <div class="mt-1 space-y-1 border border-border-strong rounded-md p-3">
        @forelse ($taxComponents as $component)
            <label class="flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" name="component_ids[]" value="{{ $component->id }}" @checked(in_array($component->id, $selectedIds)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
                {{ $component->name }} ({{ $component->rate }}%)
            </label>
        @empty
            <p class="text-sm text-ink-muted">No active tax components yet — <a href="{{ route('tax-components.create') }}" class="text-accent-400 hover:underline">create one first</a>.</p>
        @endforelse
    </div>
    <x-input-error :messages="$errors->get('component_ids')" class="mt-2" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_default" value="0">
    <input type="checkbox" id="is_default" name="is_default" value="1" @checked(old('is_default', $g->is_default ?? false)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_default" value="Store-wide default (used when a product/category has no override)" class="!mb-0" />
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $g->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
    <x-input-label for="is_active" value="Active" class="!mb-0" />
</div>
