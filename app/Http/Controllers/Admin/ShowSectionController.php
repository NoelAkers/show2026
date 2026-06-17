<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShowSectionRequest;
use App\Http\Requests\Admin\UpdateShowSectionRequest;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowSectionController extends Controller
{
    public function index(): View
    {
        $sections = ShowSection::with(['judges', 'stewards'])->ordered()->get();

        return view('admin.show-sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('admin.show-sections.create');
    }

    public function store(StoreShowSectionRequest $request): RedirectResponse
    {
        ShowSection::create($request->validated());

        return redirect()->route('admin.show-sections.index')
            ->with('success', 'Section created.');
    }

    public function edit(ShowSection $showSection): View
    {
        return view('admin.show-sections.edit', compact('showSection'));
    }

    public function update(UpdateShowSectionRequest $request, ShowSection $showSection): RedirectResponse
    {
        $showSection->update($request->validated());

        return redirect()->route('admin.show-sections.index')
            ->with('success', 'Section updated.');
    }

    public function destroy(ShowSection $showSection): RedirectResponse
    {
        if ($showSection->showClasses()->exists()) {
            return back()->with('error', 'Cannot delete a section that has classes.');
        }

        $showSection->delete();

        return redirect()->route('admin.show-sections.index')
            ->with('success', 'Section deleted.');
    }
}
