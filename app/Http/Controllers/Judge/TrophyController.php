<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with('winningEntry.exhibitor')
            ->where('judge_id', auth()->id())
            ->where('is_points_based', false)
            ->orderBy('name')
            ->get();

        return view('judge.trophies.index', compact('trophies'));
    }

    public function show(Trophy $trophy): View
    {
        abort_unless($trophy->judge_id === auth()->id(), 403);

        return view('judge.trophies.show', compact('trophy'));
    }
}
