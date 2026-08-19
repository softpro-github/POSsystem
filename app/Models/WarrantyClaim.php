<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'warranty_id',
        'claim_date',
        'issue_description',
        'resolution',
        'status',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
        ];
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
