@php $r = $discountRule ?? new \App\Models\DiscountRule(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $r->name) }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
            <option value="percentage" @selected(old('type', $r->type) === 'percentage')>Percentage (%)</option>
            <option value="fixed" @selected(old('type', $r->type) === 'fixed')>Fixed Amount</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="value" value="Value" />
        <x-text-input id="value" name="value" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('value', $r->value) }}" required />
        <x-input-error :messages="$errors->get('value')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="min_quantity" value="Minimum Quantity" />
        <x-text-input id="min_quantity" name="min_quantity" type="number" min="1" class="mt-1 block w-full" value="{{ old('min_quantity', $r->min_quantity ?? 1) }}" required />
        <x-input-error :messages="$errors->get('min_quantity')" class="mt-2" />
    </div>

    <div x-data="{ scope: '{{ old('scope', $r->scope ?? 'all') }}' }">
        <x-input-label for="scope" value="Applies To" />
        <select id="scope" name="scope" x-model="scope" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
            <option value="all">All Products</option>
            <option value="category">Specific Category</option>
            <option value="product">Specific Product</option>
        </select>
        <x-input-error :messages="$errors->get('scope')" class="mt-2" />

        <div class="mt-2" x-show="scope === 'category'">
            <select name="scope_id" class="block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('scope_id', $r->scope === 'category' ? $r->scope_id : null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-2" x-show="scope === 'product'">
            <select name="scope_id" class="block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm">
                <option value="">Select product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(old('scope_id', $r->scope === 'product' ? $r->scope_id : null) == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <x-input-label for="starts_at" value="Starts (optional)" />
        <x-text-input id="starts_at" name="starts_at" type="date" class="mt-1 block w-full" value="{{ old('starts_at', $r->starts_at?->format('Y-m-d')) }}" />
    </div>

    <div>
        <x-input-label for="ends_at" value="Ends (optional)" />
        <x-text-input id="ends_at" name="ends_at" type="date" class="mt-1 block w-full" value="{{ old('ends_at', $r->ends_at?->format('Y-m-d')) }}" />
        <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2 mt-6">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $r->is_active ?? true)) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
