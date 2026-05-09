<?php

namespace App\Models;

use Database\Factories\TrophyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

#[Fillable(['name', 'description', 'is_points_based', 'judge_id', 'winning_entry_id'])]
class Trophy extends Model
{
    /** @use HasFactory<TrophyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_points_based' => 'boolean',
        ];
    }

    public function showClasses(): BelongsToMany
    {
        return $this->belongsToMany(ShowClass::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function winningEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'winning_entry_id');
    }

    /**
     * Returns the winner(s) for this trophy.
     *
     * For judge-awarded trophies, returns the winning entry's exhibitor (or empty if not yet set).
     * For points-based trophies, returns exhibitor(s) with the most points across assigned classes.
     *
     * @return Collection<int, array{exhibitor: Exhibitor, points: int|null}>
     */
    public function winners(): Collection
    {
        if (! $this->is_points_based) {
            if (! $this->winning_entry_id) {
                return collect();
            }

            $entry = $this->winningEntry()->with('exhibitor')->first();

            return $entry
                ? collect([['exhibitor' => $entry->exhibitor, 'points' => null]])
                : collect();
        }

        $classIds = $this->showClasses()->pluck('show_classes.id');

        if ($classIds->isEmpty()) {
            return collect();
        }

        $scores = Result::query()
            ->join('entries', 'results.entry_id', '=', 'entries.id')
            ->whereIn('entries.show_class_id', $classIds)
            ->select('results.*', 'entries.exhibitor_id')
            ->get()
            ->groupBy('exhibitor_id')
            ->map(fn (Collection $results) => $results->sum(fn (Result $r) => $r->points()))
            ->filter(fn (int $points) => $points > 0);

        if ($scores->isEmpty()) {
            return collect();
        }

        $maxPoints = $scores->max();

        return $scores
            ->filter(fn (int $points) => $points === $maxPoints)
            ->map(fn (int $points, int $exhibitorId) => [
                'exhibitor' => Exhibitor::find($exhibitorId),
                'points' => $points,
            ])
            ->values();
    }
}
