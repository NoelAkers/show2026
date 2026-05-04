<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\ShowSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = $request->user()->assignedSections()->ordered()->get();

        return view('judge.sections.index', compact('sections'));
    }

    public function show(Request $request, ShowSection $showSection): View
    {
        abort_unless(
            $request->user()->assignedSections()->where('show_sections.id', $showSection->id)->exists(),
            403
        );

        $classes = $showSection->showClasses()->ordered()->withCount('entries')->get();

        return view('judge.classes.index', compact('showSection', 'classes'));
    }
}
