<?php

namespace App\Http\Controllers\Steward;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TrophyController extends Controller
{
    public function index(): View
    {
        return view('steward.trophies.index');
    }
}
