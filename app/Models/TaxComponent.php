<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaxComponent extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function taxGroups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_group_components');
    }
}
