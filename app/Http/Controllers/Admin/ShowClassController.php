<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShowClassRequest;
use App\Http\Requests\Admin\UpdateShowClassRequest;
use App\Models\Exhibitor;
use App\Models\PrizeLevel;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowClassController extends Controller
{
    public function index(ShowSection $showSection): View
    {
        $classes = $showSection->showClasses()->ordered()->get();

        return view('admin.show-classes.index', compact('showSection', 'classes'));
    }

    public function create(ShowSection $showSection): View
    {
        $prizeLevels = PrizeLevel::all();

        return view('admin.show-classes.create', compact('showSection', 'prizeLevels'));
    }

    public function store(StoreShowClassRequest $request, ShowSection $showSection): RedirectResponse
    {
        $showSection->showClasses()->create($request->validated());

        return redirect()->route('admin.show-sections.show-classes.index', $showSection)
            ->with('success', 'Class created.');
    }

    public function show(ShowSection $showSection, ShowClass $showClass): View
    {
        $showClass->load(['entries.exhibitor', 'entries.result']);
        $exhibitors = Exhibitor::orderBy('sort_name')->get();

        return view('admin.show-classes.show', compact('showSection', 'showClass', 'exhibitors'));
    }

    public function edit(ShowSection $showSection, ShowClass $showClass): View
    {
        $prizeLevels = PrizeLevel::all();

        return view('admin.show-classes.edit', compact('showSection', 'showClass', 'prizeLevels'));
    }

    public function update(UpdateShowClassRequest $request, ShowSection $showSection, ShowClass $showClass): RedirectResponse
    {
        $showClass->update($request->validated());

        return redirect()->route('admin.show-sections.show-classes.index', $showSection)
            ->with('success', 'Class updated.');
    }

    public function destroy(ShowSection $showSection, ShowClass $showClass): RedirectResponse
    {
        if ($showClass->entries()->exists()) {
            return back()->with('error', 'Cannot delete a class that has entries.');
        }

        $showClass->delete();

        return redirect()->route('admin.show-sections.show-classes.index', $showSection)
            ->with('success', 'Class deleted.');
    }
}
