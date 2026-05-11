<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ShowSection;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $sections = ShowSection::with(['showClasses' => fn ($q) => $q->ordered()])
            ->ordered()
            ->get();

        return view('public.schedule.index', compact('sections'));
    }
}
