<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTrophyRequest;
use App\Http\Requests\Admin\UpdateTrophyRequest;
use App\Models\ShowSection;
use App\Models\Trophy;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with(['showClasses'])->orderBy('name')->get();

        return view('admin.trophies.index', compact('trophies'));
    }

    public function create(): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();

        return view('admin.trophies.create', compact('sections'));
    }

    public function store(StoreTrophyRequest $request): RedirectResponse
    {
        $trophy = Trophy::create($request->only(['name', 'description']));
        $trophy->showClasses()->sync($request->validated('class_ids', []));

        return redirect()->route('admin.trophies.index')
            ->with('success', 'Trophy created.');
    }

    public function edit(Trophy $trophy): View
    {
        $sections = ShowSection::with('showClasses')->ordered()->get();

        return view('admin.trophies.edit', compact('trophy', 'sections'));
    }

    public function update(UpdateTrophyRequest $request, Trophy $trophy): RedirectResponse
    {
        $trophy->update($request->only(['name', 'description']));
        $trophy->showClasses()->sync($request->validated('class_ids', []));

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
