<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'reversed_entry_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function reference(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_entry_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalDebit(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines->sum('credit');
    }

    /**
     * Safe display label for reference_type — avoids invoking the morphTo
     * relation, since reference_type can hold non-model sentinels (e.g.
     * 'opening_balance') that would fail class resolution if queried.
     */
    public function referenceLabel(): ?string
    {
        if (! $this->reference_type) {
            return null;
        }

        if (! class_exists($this->reference_type)) {
            return ucwords(str_replace('_', ' ', $this->reference_type));
        }

        return class_basename($this->reference_type).($this->reference_id ? ' #'.$this->reference_id : '');
    }
}
