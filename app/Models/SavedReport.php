<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReport extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'report_type',
        'filters',
        'schedule_frequency',
        'recipients',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'last_run_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDue(): bool
    {
        if (! $this->schedule_frequency) {
            return false;
        }

        if (! $this->last_run_at) {
            return true;
        }

        return match ($this->schedule_frequency) {
            'daily' => $this->last_run_at->lt(now()->subDay()),
            'weekly' => $this->last_run_at->lt(now()->subWeek()),
            'monthly' => $this->last_run_at->lt(now()->subMonth()),
        };
    }
}
