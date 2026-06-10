<?php

namespace App\Models;

use Database\Factories\ResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entry_id', 'entered_by_user_id', 'placement', 'card_printed_at'])]
class Result extends Model
{
    /** @use HasFactory<ResultFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'card_printed_at' => 'datetime',
        ];
    }

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
        return config('show.placement_points')[$this->placement] ?? 0;
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

    public function prizeLabel(): string
    {
        return match ($this->placement) {
            '1st' => '1st Prize',
            '2nd' => '2nd Prize',
            '3rd' => '3rd Prize',
            'highly_commended' => 'Highly Commended',
            default => '',
        };
    }

    public function prizeColour(): string
    {
        return config('show.result_card_colours')[$this->placement] ?? '#111827';
    }

    public function isPrinted(): bool
    {
        return $this->card_printed_at !== null;
    }

    public function needsReprint(): bool
    {
        return $this->card_printed_at !== null && $this->updated_at > $this->card_printed_at;
    }
}
