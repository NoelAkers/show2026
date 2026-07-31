<?php

namespace App\Models;

use App\Enums\TrophyRestriction;
use Database\Factories\TrophyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'description', 'is_points_based', 'judge_id', 'steward_id', 'winning_entry_id', 'card_printed_at'])]
class Trophy extends Model
{
    /** @use HasFactory<TrophyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_points_based' => 'boolean',
            'card_printed_at' => 'datetime',
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

    public function steward(): BelongsTo
    {
        return $this->belongsTo(User::class, 'steward_id');
    }

    public function winningEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'winning_entry_id');
    }

    public function isPrinted(): bool
    {
        return $this->card_printed_at !== null;
    }

    public function needsReprint(): bool
    {
        return $this->card_printed_at !== null && $this->updated_at > $this->card_printed_at;
    }

    /** @return Collection<int, TrophyRestriction> */
    public function restrictions(): Collection
    {
        return collect(DB::table('trophy_restrictions')
            ->where('trophy_id', $this->id)
            ->pluck('restriction'))
            ->map(fn (string $r) => TrophyRestriction::from($r));
    }

    /**
     * Returns the winner(s) for this trophy.
     *
     * For judge-awarded trophies, returns the winning entry's exhibitor (or empty if not yet set).
     * For points-based trophies, returns exhibitor(s) with the most points across assigned classes,
     * filtered by any eligibility restrictions configured on this trophy.
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

        $restrictions = $this->restrictions();

        $scores = Result::query()
            ->join('entries', 'results.entry_id', '=', 'entries.id')
            ->join('exhibitors', 'entries.exhibitor_id', '=', 'exhibitors.id')
            ->whereIn('entries.show_class_id', $classIds)
            ->when($restrictions->contains(TrophyRestriction::Resident), fn ($q) => $q->where('exhibitors.is_resident', true))
            ->when($restrictions->contains(TrophyRestriction::Novice), fn ($q) => $q->where('exhibitors.is_novice', true))
            ->when($restrictions->contains(TrophyRestriction::Junior), fn ($q) => $q->where('exhibitors.type', 'junior'))
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

    /**
     * Returns the top exhibitors by points for manual verification.
     * Shows all exhibitors with points (not just eligible ones), marking each with their eligibility
     * so admins can spot incorrect self-classifications.
     *
     * @return Collection<int, array{exhibitor: Exhibitor, points: int, is_eligible: bool}>
     */
    public function leaderboard(int $limit = 10): Collection
    {
        if (! $this->is_points_based) {
            return collect();
        }

        $classIds = $this->showClasses()->pluck('show_classes.id');

        if ($classIds->isEmpty()) {
            return collect();
        }

        $restrictions = $this->restrictions();

        $scores = Result::query()
            ->join('entries', 'results.entry_id', '=', 'entries.id')
            ->whereIn('entries.show_class_id', $classIds)
            ->select('results.*', 'entries.exhibitor_id')
            ->get()
            ->groupBy('exhibitor_id')
            ->map(fn (Collection $results) => $results->sum(fn (Result $r) => $r->points()))
            ->filter(fn (int $points) => $points > 0)
            ->sortDesc()
            ->take($limit);

        if ($scores->isEmpty()) {
            return collect();
        }

        $exhibitors = Exhibitor::whereIn('id', $scores->keys())->get()->keyBy('id');

        return $scores
            ->map(function (int $points, int $exhibitorId) use ($exhibitors, $restrictions) {
                $exhibitor = $exhibitors->get($exhibitorId);

                return [
                    'exhibitor' => $exhibitor,
                    'points' => $points,
                    'is_eligible' => $this->exhibitorMeetsRestrictions($exhibitor, $restrictions),
                ];
            })
            ->values();
    }

    /** @param Collection<int, TrophyRestriction> $restrictions */
    private function exhibitorMeetsRestrictions(Exhibitor $exhibitor, Collection $restrictions): bool
    {
        if ($restrictions->contains(TrophyRestriction::Resident) && ! $exhibitor->is_resident) {
            return false;
        }

        if ($restrictions->contains(TrophyRestriction::Novice) && ! $exhibitor->is_novice) {
            return false;
        }

        if ($restrictions->contains(TrophyRestriction::Junior) && ! $exhibitor->isJunior()) {
            return false;
        }

        return true;
    }
}
