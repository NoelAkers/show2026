<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use Illuminate\View\View;

class ExhibitorController extends Controller
{
    public function index(): View
    {
        $exhibitors = Exhibitor::orderBy('sort_name')->get();

        return view('helper.exhibitors.index', compact('exhibitors'));
    }

    public function addEntry(Exhibitor $exhibitor): View
    {
        return view('helper.exhibitors.add-entry', compact('exhibitor'));
    }
}
