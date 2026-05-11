<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Trophy;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        $trophies = Trophy::with('showClasses')->get();

        return view('public.trophies.index', compact('trophies'));
    }
}
