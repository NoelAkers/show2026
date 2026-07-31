<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrophyCardsController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'unprinted');

        $query = Trophy::with('winningEntry.exhibitor')
            ->where('is_points_based', false)
            ->whereNotNull('winning_entry_id')
            ->orderBy('name');

        if ($filter === 'unprinted') {
            $query->where(function ($q) {
                $q->whereNull('card_printed_at')
                    ->orWhereColumn('updated_at', '>', 'card_printed_at');
            });
        }

        $trophies = $query->get();

        $allCount = Trophy::where('is_points_based', false)->whereNotNull('winning_entry_id')->count();
        $unprintedCount = Trophy::where('is_points_based', false)
            ->whereNotNull('winning_entry_id')
            ->where(function ($q) {
                $q->whereNull('card_printed_at')
                    ->orWhereColumn('updated_at', '>', 'card_printed_at');
            })->count();

        return view('admin.trophies.cards', [
            'trophies' => $trophies,
            'filter' => $filter,
            'title' => config('show.title'),
            'allCount' => $allCount,
            'unprintedCount' => $unprintedCount,
        ]);
    }

    public function markPrinted(Request $request): RedirectResponse
    {
        $ids = $request->input('trophy_ids', []);

        if (! empty($ids)) {
            Trophy::whereIn('id', $ids)->update(['card_printed_at' => now()]);
        }

        return redirect()->route('admin.trophy-cards')->with('success', count($ids).' '.str('card')->plural(count($ids)).' marked as printed.');
    }
}
