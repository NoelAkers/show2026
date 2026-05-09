<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTrophyRequest;
use App\Http\Requests\Admin\UpdateTrophyRequest;
use App\Models\Entry;
use App\Models\ShowSection;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with(['showClasses', 'judge', 'winningEntry.exhibitor'])->orderBy('name')->get();

        return view('admin.trophies.index', compact('trophies'));
    }

    public function create(): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();
        $judges = User::where('is_judge', true)->orderBy('name')->get();
        $entries = collect();

        return view('admin.trophies.create', compact('sections', 'judges', 'entries'));
    }

    public function store(StoreTrophyRequest $request): RedirectResponse
    {
        $isPointsBased = (bool) $request->input('is_points_based', true);

        $trophy = Trophy::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_points_based' => $isPointsBased,
            'judge_id' => $isPointsBased ? null : $request->validated('judge_id'),
            'winning_entry_id' => $isPointsBased ? null : $request->validated('winning_entry_id'),
        ]);

        if ($isPointsBased) {
            $trophy->showClasses()->sync($request->validated('class_ids', []));
        }

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy created.');
    }

    public function edit(Trophy $trophy): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();
        $judges = User::where('is_judge', true)->orderBy('name')->get();
        $entries = Entry::with('exhibitor')->orderBy('entry_number')->get();

        return view('admin.trophies.edit', compact('trophy', 'sections', 'judges', 'entries'));
    }

    public function update(UpdateTrophyRequest $request, Trophy $trophy): RedirectResponse
    {
        $isPointsBased = (bool) $request->input('is_points_based', true);

        $trophy->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_points_based' => $isPointsBased,
            'judge_id' => $isPointsBased ? null : $request->validated('judge_id'),
            'winning_entry_id' => $isPointsBased ? null : $request->validated('winning_entry_id'),
        ]);

        if ($isPointsBased) {
            $trophy->showClasses()->sync($request->validated('class_ids', []));
        } else {
            $trophy->showClasses()->detach();
        }

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy updated.');
    }

    public function destroy(Trophy $trophy): RedirectResponse
    {
        $trophy->showClasses()->detach();
        $trophy->delete();

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy deleted.');
    }
}
