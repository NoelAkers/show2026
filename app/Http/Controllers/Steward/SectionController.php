<?php

namespace App\Http\Controllers\Steward;

use App\Http\Controllers\Controller;
use App\Models\ShowSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = $request->user()->assignedSections()->ordered()->get();

        return view('steward.sections.index', compact('sections'));
    }

    public function show(ShowSection $showSection): View
    {
        return view('steward.classes.index', compact('showSection'));
    }
}
