<?php

namespace App\Models;

use Database\Factories\ResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entry_id', 'entered_by_user_id', 'placement', 'notes'])]
class Result extends Model
{
    /** @use HasFactory<ResultFactory> */
    use HasFactory;

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }

    public function points(): int
    {
        return match ($this->placement) {
            '1st' => 3,
            '2nd' => 2,
            '3rd' => 1,
            default => 0,
        };
    }

    public function placementLabel(): string
    {
        return match ($this->placement) {
            '1st' => '1st Place',
            '2nd' => '2nd Place',
            '3rd' => '3rd Place',
            'highly_commended' => 'Highly Commended',
            default => 'No Placement',
        };
    }
}
