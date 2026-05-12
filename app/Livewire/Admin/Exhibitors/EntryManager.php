<?php

namespace App\Livewire\Admin\Exhibitors;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\View\View;
use Livewire\Component;

class EntryManager extends Component
{
    public Exhibitor $exhibitor;

    /** @var array<int, int> Desired total entry count per show class */
    public array $quantities = [];

    public ?string $statusMessage = null;

    public bool $statusIsSuccess = true;

    public function mount(): void
    {
        $this->quantities = $this->currentCounts();
    }

    public function increment(int $classId): void
    {
        $max = ShowClass::findOrFail($classId)->max_entries_per_exhibitor;
        $this->quantities[$classId] = min(($this->quantities[$classId] ?? 0) + 1, $max);
    }

    public function decrement(int $classId): void
    {
        $locked = $this->lockedCountForClass($classId);
        $this->quantities[$classId] = max(($this->quantities[$classId] ?? 0) - 1, $locked);
    }

    public function save(): void
    {
        $entriesByClass = $this->exhibitor->entries()
            ->with('result')
            ->get()
            ->groupBy('show_class_id');

        $maxCounts = ShowClass::pluck('max_entries_per_exhibitor', 'id');

        $added = 0;
        $removed = 0;

        foreach ($this->quantities as $classId => $target) {
            $classId = (int) $classId;
            $existing = $entriesByClass->get($classId) ?? collect();
            $current = $existing->count();

            $locked = $existing->filter(fn ($e) => $e->result !== null)->count();
            $target = max($locked, min((int) $target, $maxCounts->get($classId, 0)));

            if ($target > $current) {
                $toAdd = $target - $current;
                for ($i = 0; $i < $toAdd; $i++) {
                    Entry::create([
                        'show_class_id' => $classId,
                        'exhibitor_id' => $this->exhibitor->id,
                    ]);
                }
                $added += $toAdd;
            } elseif ($target < $current) {
                $deletable = $existing
                    ->filter(fn ($e) => $e->result === null)
                    ->sortByDesc('entry_number')
                    ->take($current - $target);

                Entry::whereIn('id', $deletable->pluck('id'))->delete();
                $removed += $deletable->count();
            }
        }

        $this->quantities = $this->currentCounts();

        if ($added === 0 && $removed === 0) {
            $this->statusMessage = 'No changes to save.';
            $this->statusIsSuccess = false;
        } else {
            $parts = [];
            if ($added > 0) {
                $parts[] = $added === 1 ? '1 entry added' : "{$added} entries added";
            }
            if ($removed > 0) {
                $parts[] = $removed === 1 ? '1 entry removed' : "{$removed} entries removed";
            }
            $this->statusMessage = implode(', ', $parts).'.';
            $this->statusIsSuccess = true;
        }
    }

    public function render(): View
    {
        $sections = ShowSection::with(['showClasses' => fn ($q) => $q->ordered()])
            ->ordered()
            ->get();

        $this->exhibitor->load(['entries.showClass.showSection', 'entries.result']);

        $lockedCounts = $this->exhibitor->entries
            ->filter(fn ($e) => $e->result !== null)
            ->groupBy('show_class_id')
            ->map->count()
            ->all();

        $maxCounts = ShowClass::pluck('max_entries_per_exhibitor', 'id')->all();

        return view('livewire.admin.exhibitors.entry-manager', compact('sections', 'lockedCounts', 'maxCounts'));
    }

    /** @return array<int, int> */
    private function currentCounts(): array
    {
        return $this->exhibitor->entries()
            ->get()
            ->groupBy('show_class_id')
            ->map->count()
            ->all();
    }

    private function lockedCountForClass(int $classId): int
    {
        return $this->exhibitor->entries()
            ->where('show_class_id', $classId)
            ->whereHas('result')
            ->count();
    }
}
