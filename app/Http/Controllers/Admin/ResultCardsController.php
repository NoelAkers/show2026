<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultCardsController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'unprinted');
        $showClassId = $request->query('show_class_id');
        $exhibitorId = $request->query('exhibitor_id');

        $query = Result::with([
            'entry.showClass.showSection',
            'entry.exhibitor',
        ])
            ->join('entries', 'results.entry_id', '=', 'entries.id')
            ->join('show_classes', 'entries.show_class_id', '=', 'show_classes.id')
            ->join('show_sections', 'show_classes.show_section_id', '=', 'show_sections.id')
            ->whereNotNull('results.placement')
            ->orderBy('show_sections.sort_order')
            ->orderBy('show_classes.sort_order')
            ->orderByRaw("CASE results.placement WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN '3rd' THEN 3 WHEN 'highly_commended' THEN 4 ELSE 5 END")
            ->orderBy('entries.entry_number')
            ->select('results.*');

        if ($showClassId) {
            $query->where('entries.show_class_id', $showClassId);
        }

        if ($exhibitorId) {
            $query->where('entries.exhibitor_id', $exhibitorId);
        }

        if ($filter === 'unprinted') {
            $query->where(function ($q) {
                $q->whereNull('results.card_printed_at')
                    ->orWhereColumn('results.updated_at', '>', 'results.card_printed_at');
            });
        }

        $results = $query->get();

        $allCount = Result::whereNotNull('placement')->count();
        $unprintedCount = Result::whereNotNull('placement')
            ->where(function ($q) {
                $q->whereNull('card_printed_at')
                    ->orWhereColumn('updated_at', '>', 'card_printed_at');
            })->count();

        return view('admin.results.cards', [
            'results' => $results,
            'filter' => $filter,
            'title' => config('show.title'),
            'allCount' => $allCount,
            'unprintedCount' => $unprintedCount,
            'showClass' => $showClassId ? ShowClass::find($showClassId) : null,
            'exhibitor' => $exhibitorId ? Exhibitor::find($exhibitorId) : null,
        ]);
    }

    public function markPrinted(Request $request): RedirectResponse
    {
        $ids = $request->input('result_ids', []);

        if (! empty($ids)) {
            Result::whereIn('id', $ids)->update(['card_printed_at' => now()]);
        }

        return redirect()->route('admin.result-cards')->with('success', count($ids).' '.str('card')->plural(count($ids)).' marked as printed.');
    }
}
