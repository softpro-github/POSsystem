<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'device_type',
        'device_brand',
        'device_model',
        'imei_serial',
        'issue_description',
        'status',
        'technician_id',
        'estimated_cost',
        'final_cost',
        'received_date',
        'completed_date',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
            'received_date' => 'date',
            'completed_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RepairStatusLog::class);
    }
}
