<?php

namespace App\Models;

use Database\Factories\TrophyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

#[Fillable(['name', 'description'])]
class Trophy extends Model
{
    /** @use HasFactory<TrophyFactory> */
    use HasFactory;

    public function showClasses(): BelongsToMany
    {
        return $this->belongsToMany(ShowClass::class);
    }

    /**
     * Returns exhibitor(s) with the most points across this trophy's classes.
     *
     * @return Collection<int, array{exhibitor: Exhibitor, points: int}>
     */
    public function winners(): Collection
    {
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
