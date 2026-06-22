<?php

namespace App\Http\Controllers\Steward;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with('winningEntry.exhibitor')
            ->where('steward_id', auth()->id())
            ->where('is_points_based', false)
            ->orderBy('name')
            ->get();

        return view('steward.trophies.index', compact('trophies'));
    }

    public function show(Trophy $trophy): View
    {
        abort_unless($trophy->steward_id === auth()->id(), 403);

        return view('steward.trophies.show', compact('trophy'));
    }
}
