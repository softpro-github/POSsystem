<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'year',
        'month',
        'is_locked',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }
}
