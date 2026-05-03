<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEntryRequest;
use App\Models\Entry;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request, ShowSection $showSection, ShowClass $showClass): RedirectResponse
    {
        Entry::create([
            'show_class_id' => $showClass->id,
            'exhibitor_id' => $request->validated('exhibitor_id'),
        ]);

        return redirect()->route('admin.show-sections.show-classes.show', [$showSection, $showClass])
            ->with('success', 'Entry added.');
    }

    public function destroy(ShowSection $showSection, ShowClass $showClass, Entry $entry): RedirectResponse
    {
        if ($entry->hasResult()) {
            return back()->with('error', 'Cannot delete an entry that already has a result.');
        }

        $entry->delete();

        return redirect()->route('admin.show-sections.show-classes.show', [$showSection, $showClass])
            ->with('success', 'Entry deleted.');
    }
}
