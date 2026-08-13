<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\View\View;

class TrophyListController extends Controller
{
    public function __invoke(): View
    {
        $trophies = Trophy::with(['winningEntry.exhibitor'])->orderBy('id')->get();

        return view('admin.trophies.list', [
            'trophies' => $trophies,
            'title' => config('show.title'),
        ]);
    }
}
