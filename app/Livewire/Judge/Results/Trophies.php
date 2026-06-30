<?php

namespace App\Livewire\Judge\Results;

use App\Models\Entry;
use App\Models\Trophy;
use Illuminate\View\View;
use Livewire\Component;

class Trophies extends Component
{
    public array $winningEntries = [];

    public function mount(): void
    {
        Trophy::with('winningEntry')
            ->where('judge_id', auth()->id())
            ->where('is_points_based', false)
            ->get()
            ->each(function (Trophy $trophy) {
                $this->winningEntries[$trophy->id] = $trophy->winningEntry?->formatted_entry_number ?? '';
            });
    }

    public function saveTrophy(int $trophyId): void
    {
        $trophy = Trophy::findOrFail($trophyId);
        abort_unless($trophy->judge_id === auth()->id(), 403);

        $value = $this->winningEntries[$trophyId] ?? '';

        if ($value === '') {
            $trophy->update(['winning_entry_id' => null]);
            $this->resetErrorBag("winningEntries.{$trophyId}");

            return;
        }

        $entry = Entry::where('entry_number', (int) $value)->first();

        if (! $entry) {
            $this->addError("winningEntries.{$trophyId}", 'Entry number not found.');

            return;
        }

        $trophy->update(['winning_entry_id' => $entry->id]);
        $this->winningEntries[$trophyId] = $entry->formatted_entry_number;
        $this->resetErrorBag("winningEntries.{$trophyId}");
    }

    public function render(): View
    {
        $trophies = Trophy::with('winningEntry')
            ->where('judge_id', auth()->id())
            ->where('is_points_based', false)
            ->orderBy('name')
            ->get();

        return view('livewire.judge.results.trophies', ['trophies' => $trophies]);
    }
}
