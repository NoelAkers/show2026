<?php

namespace App\Livewire\Admin;

use App\Models\Entry;
use App\Models\Trophy;
use Illuminate\View\View;
use Livewire\Component;

class Leaderboard extends Component
{
    /** @var array<int, string> */
    public array $winningEntries = [];

    public function mount(): void
    {
        Trophy::with('winningEntry')
            ->where('is_points_based', false)
            ->get()
            ->each(function (Trophy $trophy) {
                $this->winningEntries[$trophy->id] = $trophy->winningEntry?->formatted_entry_number ?? '';
            });
    }

    public function saveTrophy(int $trophyId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $trophy = Trophy::findOrFail($trophyId);
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
        $trophies = Trophy::with(['winningEntry.exhibitor'])->orderBy('id')->get();

        return view('livewire.admin.leaderboard', compact('trophies'));
    }
}
