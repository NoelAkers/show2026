<?php

namespace App\Livewire\Judge\Results;

use App\Models\Entry;
use App\Models\Trophy as TrophyModel;
use Illuminate\View\View;
use Livewire\Component;

class Trophy extends Component
{
    public TrophyModel $trophy;

    public ?int $winningEntryId = null;

    public bool $saved = false;

    public function mount(TrophyModel $trophy): void
    {
        abort_unless($trophy->judge_id === auth()->id(), 403);

        $this->trophy = $trophy;
        $this->winningEntryId = $trophy->winning_entry_id;
    }

    public function save(): void
    {
        $this->trophy->update(['winning_entry_id' => $this->winningEntryId]);
        $this->saved = true;
    }

    public function render(): View
    {
        $entries = Entry::with('exhibitor')->orderBy('entry_number')->get();

        return view('livewire.judge.results.trophy', compact('entries'));
    }
}
