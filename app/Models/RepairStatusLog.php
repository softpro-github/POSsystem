<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairStatusLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'repair_ticket_id',
        'status',
        'note',
        'changed_by',
    ];

    public function repairTicket(): BelongsTo
    {
        return $this->belongsTo(RepairTicket::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
