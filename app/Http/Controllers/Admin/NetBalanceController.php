<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use Illuminate\View\View;

class NetBalanceController extends Controller
{
    public function __invoke(): View
    {
        $exhibitors = Exhibitor::orderBy('sort_name')->get();

        $totalFeePence = $exhibitors->sum(fn (Exhibitor $exhibitor) => $exhibitor->feeOwedPence());
        $totalWinningsPence = $exhibitors->sum(fn (Exhibitor $exhibitor) => $exhibitor->winningsPence());
        $totalNetPence = $totalFeePence - $totalWinningsPence;

        return view('admin.net-balances.index', compact(
            'exhibitors',
            'totalFeePence',
            'totalWinningsPence',
            'totalNetPence',
        ));
    }
}
