<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'tax_group_id',
        'name',
        'sku',
        'barcode',
        'description',
        'image_path',
        'cost_price',
        'selling_price',
        'track_serial',
        'reorder_level',
        'is_active',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::url($this->image_path) : null;
    }

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'track_serial' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TaxGroup::class);
    }

    /**
     * Effective tax group: product-level override wins, else the category's
     * group, else the store-wide Default group.
     */
    public function resolveTaxGroup(): ?TaxGroup
    {
        return $this->taxGroup ?? $this->category?->taxGroup ?? TaxGroup::defaultGroup();
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function productStores(): HasMany
    {
        return $this->hasMany(ProductStore::class);
    }

    /**
     * Quantity at the current request's store (see current_store()). Reads
     * as 0 when there's no current store context or no stock row yet —
     * behaves like the old flat `quantity` column for every existing
     * read-only call site (views, reports, dashboard).
     */
    public function getQuantityAttribute(): int
    {
        return $this->stockAt(current_store())?->quantity ?? 0;
    }

    public function stockAt(?Store $store): ?ProductStore
    {
        if (! $store) {
            return null;
        }

        return $this->relationLoaded('productStores')
            ? $this->productStores->firstWhere('store_id', $store->id)
            : $this->productStores()->where('store_id', $store->id)->first();
    }

    public function totalQuantity(): int
    {
        return (int) $this->productStores()->sum('quantity');
    }

    public function scopeLowStock($query, ?Store $store = null)
    {
        $storeId = ($store ?? current_store())?->id;

        return $query->whereHas('productStores', function ($q) use ($storeId) {
            $q->when($storeId, fn ($q) => $q->where('store_id', $storeId))
                ->whereColumn('product_stores.quantity', '<=', 'products.reorder_level');
        });
    }
}
