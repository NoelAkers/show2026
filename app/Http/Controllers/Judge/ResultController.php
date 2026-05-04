<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResultRequest;
use App\Http\Requests\UpdateResultRequest;
use App\Models\Entry;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request, ShowSection $showSection, ShowClass $showClass): View
    {
        abort_unless(
            $request->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );
        abort_unless($showClass->show_section_id === $showSection->id, 404);

        $showClass->load(['entries.exhibitor', 'entries.result']);

        return view('judge.results.index', compact('showSection', 'showClass'));
    }

    public function store(StoreResultRequest $request, ShowSection $showSection, ShowClass $showClass): RedirectResponse
    {
        abort_unless(
            $request->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );
        abort_unless($showClass->show_section_id === $showSection->id, 404);

        $entry = Entry::where('id', $request->integer('entry_id'))
            ->where('show_class_id', $showClass->id)
            ->firstOrFail();

        $entry->result()->create([
            'entered_by_user_id' => $request->user()->id,
            'placement' => $request->input('placement') ?: null,
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Result saved.');
    }

    public function update(UpdateResultRequest $request, ShowSection $showSection, ShowClass $showClass, Result $result): RedirectResponse
    {
        abort_unless(
            $request->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );
        abort_unless($showClass->show_section_id === $showSection->id, 404);
        abort_unless($result->entry->show_class_id === $showClass->id, 403);

        $result->update([
            'entered_by_user_id' => $request->user()->id,
            'placement' => $request->input('placement') ?: null,
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Result updated.');
    }
}
