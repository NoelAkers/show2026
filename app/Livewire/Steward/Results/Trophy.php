<?php

namespace App\Livewire\Steward\Results;

use App\Models\Entry;
use App\Models\Trophy as TrophyModel;
use Illuminate\View\View;
use Livewire\Component;

class Trophy extends Component
{
    public TrophyModel $trophy;

    public string $winningEntryNumber = '';

    public bool $saved = false;

    public function mount(TrophyModel $trophy): void
    {
        abort_unless($trophy->steward_id === auth()->id(), 403);

        $this->trophy = $trophy;
        $this->winningEntryNumber = $trophy->winningEntry?->formatted_entry_number ?? '';
    }

    public function save(): void
    {
        if ($this->winningEntryNumber === '') {
            $this->trophy->update(['winning_entry_id' => null]);
            $this->saved = true;

            return;
        }

        $entry = Entry::where('entry_number', (int) $this->winningEntryNumber)->first();

        if (! $entry) {
            $this->addError('winningEntryNumber', 'Entry number not found.');

            return;
        }

        $this->trophy->update(['winning_entry_id' => $entry->id]);
        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.steward.results.trophy');
    }
}
