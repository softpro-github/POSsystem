@php $p = $product ?? new \App\Models\Product(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $p->name) }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
            <option value="">— None —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $p->category_id) == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sku" value="SKU (optional)" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" value="{{ old('sku', $p->sku) }}" />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="barcode" value="Barcode (optional)" />
        <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full" value="{{ old('barcode', $p->barcode) }}" />
        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="brand_id" value="Brand (optional)" />
        <select id="brand_id" name="brand_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
            <option value="">— None —</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected(old('brand_id', $p->brand_id) == $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="unit_id" value="Unit" />
        <select id="unit_id" name="unit_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
            <option value="">Select a unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $p->unit_id) == $unit->id)>{{ $unit->name }}{{ $unit->abbreviation ? ' ('.$unit->abbreviation.')' : '' }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cost_price" value="Cost Price" />
        <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('cost_price', $p->cost_price) }}" required />
        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="selling_price" value="Selling Price" />
        <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('selling_price', $p->selling_price) }}" required />
        <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="reorder_level" value="Reorder Level" />
        <x-text-input id="reorder_level" name="reorder_level" type="number" min="0" class="mt-1 block w-full" value="{{ old('reorder_level', $p->reorder_level ?? 0) }}" required />
        <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tax_group_id" value="Tax Group (optional, overrides category/store default)" />
        <select id="tax_group_id" name="tax_group_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
            <option value="">— Use category/store default —</option>
            @foreach ($taxGroups as $group)
                <option value="{{ $group->id }}" @selected(old('tax_group_id', $p->tax_group_id) == $group->id)>{{ $group->name }} ({{ $group->totalRate() }}%)</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tax_group_id')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2 mt-6">
        <input type="hidden" name="track_serial" value="0">
        <input type="checkbox" id="track_serial" name="track_serial" value="1" @checked(old('track_serial', $p->track_serial)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
        <x-input-label for="track_serial" value="Track IMEI/Serial numbers" class="!mb-0" />
    </div>

    @if ($product)
        <div class="flex items-center gap-2 mt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $p->is_active)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
            <x-input-label for="is_active" value="Active" class="!mb-0" />
        </div>
    @endif
</div>

<div>
    <x-input-label for="description" value="Description (optional)" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('description', $p->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div>
    <x-input-label for="image" value="Product Photo (optional, shown on the POS screen)" />
    @if ($p->image_url)
        <div class="flex items-center gap-3 mt-2 mb-2">
            <img src="{{ $p->image_url }}" alt="" class="h-16 w-16 rounded-md object-cover border border-border">
            <label class="flex items-center gap-2 text-xs text-ink-muted">
                <input type="hidden" name="remove_image" value="0">
                <input type="checkbox" name="remove_image" value="1" class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
                Remove current photo
            </label>
        </div>
    @endif
    <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-ink-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-surface-hover file:text-ink file:text-xs hover:file:bg-zinc-700">
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
</div>
