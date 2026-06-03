<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowClass;
use Illuminate\View\View;

class ClassCardsController extends Controller
{
    public function __invoke(): View
    {
        $classes = ShowClass::with('showSection')
            ->join('show_sections', 'show_classes.show_section_id', '=', 'show_sections.id')
            ->orderBy('show_sections.sort_order')
            ->orderBy('show_classes.sort_order')
            ->select('show_classes.*')
            ->get();

        return view('admin.show-classes.cards', compact('classes'));
    }
}
