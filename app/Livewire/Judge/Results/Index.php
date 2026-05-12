<?php

namespace App\Livewire\Judge\Results;

use App\Models\Entry;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public ShowSection $showSection;

    public ShowClass $showClass;

    /** @var array<int, string> */
    public array $placements = [];

    /** @var array<int, string> */
    public array $notes = [];

    public function mount(ShowSection $showSection, ShowClass $showClass): void
    {
        abort_unless(
            auth()->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );
        abort_unless($showClass->show_section_id === $showSection->id, 404);

        $this->showSection = $showSection;
        $this->showClass = $showClass;

        $showClass->load(['entries' => fn ($q) => $q->orderBy('entry_number'), 'entries.result']);

        foreach ($showClass->entries as $entry) {
            $this->placements[$entry->id] = $entry->result?->placement ?? '';
            $this->notes[$entry->id] = $entry->result?->notes ?? '';
        }
    }

    public function save(): void
    {
        $topThree = ['1st', '2nd', '3rd'];
        $used = [];

        foreach ($this->placements as $placement) {
            if (! in_array($placement, $topThree, strict: true)) {
                continue;
            }

            if (in_array($placement, $used, strict: true)) {
                $label = match ($placement) {
                    '1st' => '1st place',
                    '2nd' => '2nd place',
                    '3rd' => '3rd place',
                };
                $this->addError('placements', "More than one entry has been awarded {$label}.");

                return;
            }

            $used[] = $placement;
        }

        $entries = Entry::whereIn('id', array_keys($this->placements))
            ->where('show_class_id', $this->showClass->id)
            ->get();

        foreach ($entries as $entry) {
            $entry->result()->updateOrCreate([], [
                'entered_by_user_id' => auth()->id(),
                'placement' => $this->placements[$entry->id] ?: null,
                'notes' => $this->notes[$entry->id] ?: null,
            ]);
        }

        session()->flash('success', 'Results saved.');

        $this->redirect(route('judge.results.index', [$this->showSection, $this->showClass]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.judge.results.index');
    }
}
