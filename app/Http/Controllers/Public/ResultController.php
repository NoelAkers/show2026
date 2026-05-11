<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ShowSection;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(): View
    {
        $sections = ShowSection::with([
            'showClasses' => fn ($q) => $q->ordered()->with([
                'entries' => fn ($q) => $q
                    ->whereHas('result', fn ($q) => $q->whereNotNull('placement'))
                    ->with(['result', 'exhibitor']),
            ]),
        ])->ordered()->get();

        return view('public.results.index', compact('sections'));
    }
}
