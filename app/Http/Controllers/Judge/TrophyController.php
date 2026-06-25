<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        return view('judge.trophies.index');
    }
}
