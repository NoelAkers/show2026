<?php

namespace App\Livewire\Judge\Results;

use App\Models\Entry;
use App\Models\ShowSection;
use Illuminate\View\View;
use Livewire\Component;

class Section extends Component
{
    public ShowSection $showSection;

    /** @var array<int, string> */
    public array $placements = [];

    /** @var array<int, string> */
    public array $notes = [];

    /** @var array<int, string> */
    public array $classErrors = [];

    public function mount(ShowSection $showSection): void
    {
        abort_unless(
            auth()->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );

        $this->showSection = $showSection;

        $showSection->load([
            'showClasses' => fn ($q) => $q->ordered(),
            'showClasses.entries' => fn ($q) => $q->orderBy('entry_number'),
            'showClasses.entries.result',
        ]);

        foreach ($showSection->showClasses as $class) {
            foreach ($class->entries as $entry) {
                $this->placements[$entry->id] = $entry->result?->placement ?? '';
                $this->notes[$entry->id] = $entry->result?->notes ?? '';
            }
        }
    }

    public function saveClass(int $showClassId): void
    {
        unset($this->classErrors[$showClassId]);

        $class = $this->showSection->showClasses->firstWhere('id', $showClassId);

        if (! $class) {
            return;
        }

        $topThree = ['1st', '2nd', '3rd'];
        $used = [];

        foreach ($class->entries as $entry) {
            $placement = $this->placements[$entry->id] ?? '';

            if (! in_array($placement, $topThree, strict: true)) {
                continue;
            }

            if (in_array($placement, $used, strict: true)) {
                $label = match ($placement) {
                    '1st' => '1st place',
                    '2nd' => '2nd place',
                    '3rd' => '3rd place',
                };
                $this->classErrors[$showClassId] = "More than one entry has been awarded {$label} in {$class->name}.";

                return;
            }

            $used[] = $placement;
        }

        $entryIds = $class->entries->pluck('id')->all();

        Entry::whereIn('id', $entryIds)
            ->where('show_class_id', $showClassId)
            ->get()
            ->each(function (Entry $entry) {
                $entry->result()->updateOrCreate([], [
                    'entered_by_user_id' => auth()->id(),
                    'placement' => $this->placements[$entry->id] ?: null,
                    'notes' => $this->notes[$entry->id] ?: null,
                ]);
            });

        $this->dispatch('class-saved', classId: $showClassId);
    }

    public function render(): View
    {
        $this->showSection->load([
            'showClasses' => fn ($q) => $q->ordered(),
            'showClasses.entries' => fn ($q) => $q->orderBy('entry_number'),
            'showClasses.entries.result',
        ]);

        return view('livewire.judge.results.section');
    }
}
