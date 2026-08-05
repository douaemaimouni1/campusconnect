<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClubPresidencyTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'current_president_id',
        'proposed_president_id',
        'status',
        'initiated_by',
        'reason',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function currentPresident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_president_id');
    }

    public function proposedPresident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_president_id');
    }

    /**
     * Ne retourne que les propositions en attente de réponse.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}