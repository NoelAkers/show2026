<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\ShowClass;
use App\Models\ShowSection;
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

        return view('judge.results.index', compact('showSection', 'showClass'));
    }
}
