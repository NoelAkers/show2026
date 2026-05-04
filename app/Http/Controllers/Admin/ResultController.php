<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResultRequest;
use App\Http\Requests\UpdateResultRequest;
use App\Models\Entry;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;

class ResultController extends Controller
{
    public function store(StoreResultRequest $request, ShowSection $showSection, ShowClass $showClass): RedirectResponse
    {
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
        $result->update([
            'entered_by_user_id' => $request->user()->id,
            'placement' => $request->input('placement') ?: null,
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Result updated.');
    }

    public function destroy(ShowSection $showSection, ShowClass $showClass, Result $result): RedirectResponse
    {
        $result->delete();

        return back()->with('success', 'Result deleted.');
    }
}
