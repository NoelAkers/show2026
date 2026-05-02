<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = $request->user()->assignedSections()->ordered()->get();

        return view('judge.sections.index', compact('sections'));
    }
}
