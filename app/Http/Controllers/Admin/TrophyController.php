<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TrophyRestriction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTrophyRequest;
use App\Http\Requests\Admin\UpdateTrophyRequest;
use App\Models\Entry;
use App\Models\ShowSection;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with(['showClasses', 'judge', 'steward', 'winningEntry.exhibitor'])->orderBy('name')->get();

        return view('admin.trophies.index', compact('trophies'));
    }

    public function create(): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();
        $judges = User::where('role', UserRole::Judge)->orderBy('name')->get();
        $stewards = User::where('role', UserRole::Steward)->orderBy('name')->get();
        $entries = collect();

        return view('admin.trophies.create', compact('sections', 'judges', 'stewards', 'entries'));
    }

    public function store(StoreTrophyRequest $request): RedirectResponse
    {
        $isPointsBased = (bool) $request->input('is_points_based', true);

        $trophy = Trophy::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_points_based' => $isPointsBased,
            'judge_id' => $isPointsBased ? null : $request->validated('judge_id'),
            'steward_id' => $isPointsBased ? null : $request->validated('steward_id'),
            'winning_entry_id' => $isPointsBased ? null : $request->validated('winning_entry_id'),
        ]);

        if ($isPointsBased) {
            $trophy->showClasses()->sync($request->validated('class_ids', []));
            $this->syncRestrictions($trophy->id, $request->validated('restrictions', []));
        }

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy created.');
    }

    public function edit(Trophy $trophy): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();
        $judges = User::where('role', UserRole::Judge)->orderBy('name')->get();
        $stewards = User::where('role', UserRole::Steward)->orderBy('name')->get();
        $entries = Entry::with('exhibitor')->orderBy('entry_number')->get();
        $trophyRestrictions = $trophy->restrictions()->map(fn (TrophyRestriction $r) => $r->value)->toArray();

        return view('admin.trophies.edit', compact('trophy', 'sections', 'judges', 'stewards', 'entries', 'trophyRestrictions'));
    }

    public function update(UpdateTrophyRequest $request, Trophy $trophy): RedirectResponse
    {
        $isPointsBased = (bool) $request->input('is_points_based', true);

        $trophy->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_points_based' => $isPointsBased,
            'judge_id' => $isPointsBased ? null : $request->validated('judge_id'),
            'steward_id' => $isPointsBased ? null : $request->validated('steward_id'),
            'winning_entry_id' => $isPointsBased ? null : $request->validated('winning_entry_id'),
        ]);

        if ($isPointsBased) {
            $trophy->showClasses()->sync($request->validated('class_ids', []));
            $this->syncRestrictions($trophy->id, $request->validated('restrictions', []));
        } else {
            $trophy->showClasses()->detach();
            $this->syncRestrictions($trophy->id, []);
        }

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy updated.');
    }

    public function leaderboard(Trophy $trophy): View
    {
        $rows = $trophy->leaderboard();
        $restrictions = $trophy->restrictions();

        return view('admin.trophies.leaderboard', compact('trophy', 'rows', 'restrictions'));
    }

    public function destroy(Trophy $trophy): RedirectResponse
    {
        $trophy->showClasses()->detach();
        $trophy->delete();

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy deleted.');
    }

    /** @param array<string> $restrictions */
    private function syncRestrictions(int $trophyId, array $restrictions): void
    {
        DB::table('trophy_restrictions')->where('trophy_id', $trophyId)->delete();

        foreach ($restrictions as $restriction) {
            DB::table('trophy_restrictions')->insert([
                'trophy_id' => $trophyId,
                'restriction' => TrophyRestriction::from($restriction)->value,
            ]);
        }
    }
}
