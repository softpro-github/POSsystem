<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Products</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong text-ink-muted rounded-md text-sm hover:bg-surface-hover">Categories</a>
                <a href="{{ route('labels.create') }}" class="inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong text-ink-muted rounded-md text-sm hover:bg-surface-hover">Print Labels</a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Add Product</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU or barcode" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm flex-1 min-w-[200px]">
                <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
                    <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
                    Low stock only
                </label>
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Filter</button>
                @if (request()->hasAny(['search', 'low_stock']))
                    <a href="{{ route('products.index') }}" class="text-sm text-ink-muted hover:underline">Clear</a>
                @endif
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4 text-right">Cost</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-right">Qty</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-b border-border last:border-0 {{ !$product->is_active ? 'opacity-50' : '' }}">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" class="h-9 w-9 rounded-md object-cover border border-border shrink-0">
                                        @else
                                            <div class="h-9 w-9 rounded-md bg-surface-hover shrink-0"></div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-ink">{{ $product->name }}</div>
                                            <div class="text-xs text-ink-muted">{{ $product->sku }} @if($product->barcode) &middot; {{ $product->barcode }} @endif</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">{{ $product->category?->name ?? '—' }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$product->cost_price" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$product->selling_price" /></td>
                                <td class="py-3 px-4 text-right">
                                    <span class="{{ $product->quantity <= $product->reorder_level ? 'text-red-400 font-semibold' : '' }}">{{ $product->quantity }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <details class="relative">
                                            <summary class="cursor-pointer text-ink-muted hover:underline list-none">Adjust</summary>
                                            <form action="{{ route('stock.adjust') }}" method="POST" class="absolute right-0 z-10 mt-2 w-56 bg-surface-raised border border-border rounded-md shadow-lg p-3 space-y-2 text-left">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="number" name="delta" placeholder="+/- qty" required class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
                                                <select name="adjustment_reason_id" required class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
                                                    <option value="">Reason...</option>
                                                    @foreach ($adjustmentReasons as $reason)
                                                        <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="note" placeholder="Note (optional)" class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
                                                <button type="submit" class="w-full bg-accent-500 text-zinc-950 text-sm rounded-md py-1 hover:bg-accent-400">Apply</button>
                                            </form>
                                        </details>
                                        <a href="{{ route('products.edit', $product) }}" class="text-accent-400 hover:text-accent-300 hover:underline">Edit</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
