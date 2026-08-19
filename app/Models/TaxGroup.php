<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxGroup extends Model
{
    protected $fillable = [
        'name',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(TaxComponent::class, 'tax_group_components');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function totalRate(): float
    {
        if ($this->relationLoaded('components')) {
            return (float) $this->components->where('is_active', true)->sum('rate');
        }

        return (float) $this->components()->where('is_active', true)->sum('rate');
    }

    public static function defaultGroup(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
