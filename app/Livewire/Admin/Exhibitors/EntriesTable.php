<?php

namespace App\Livewire\Admin\Exhibitors;

use App\Models\Exhibitor;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class EntriesTable extends Component
{
    public Exhibitor $exhibitor;

    public string $sortBy = 'entry_number';

    public string $sortDirection = 'asc';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render(): View
    {
        $this->exhibitor->load(['entries.showClass.showSection', 'entries.result']);

        return view('livewire.admin.exhibitors.entries-table', [
            'entries' => $this->sortedEntries(),
        ]);
    }

    private function sortedEntries(): Collection
    {
        $key = match ($this->sortBy) {
            'section', 'class' => fn ($entry) => [
                $entry->showClass->showSection->sort_order,
                $entry->showClass->sort_order,
            ],
            'placement' => fn ($entry) => $entry->result?->placementRank() ?? 5,
            default => fn ($entry) => $entry->entry_number,
        };

        return ($this->sortDirection === 'asc'
            ? $this->exhibitor->entries->sortBy($key)
            : $this->exhibitor->entries->sortByDesc($key))->values();
    }
}
