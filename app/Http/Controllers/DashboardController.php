<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $user->isJudge()) {
            return redirect()->route('judge.sections.index');
        }

        if (! $user->isAdmin() && $user->isSteward()) {
            return redirect()->route('steward.sections.index');
        }

        $sectionCount = ShowSection::count();
        $classCount = ShowClass::count();
        $classesWithEntries = ShowClass::has('entries')->count();
        $classesAwaitingJudging = ShowClass::has('entries')->whereDoesntHave('entries.result')->count();
        $adultCount = Exhibitor::where('type', 'adult')->count();
        $juniorCount = Exhibitor::where('type', 'junior')->count();
        $entryCount = Entry::count();
        $resultsEntered = Result::whereNotNull('placement')->count();
        $resultsOutstanding = Entry::whereDoesntHave('result')->count();
        $paidCount = Exhibitor::where('type', 'adult')->where('has_paid', true)->count();
        $unpaidCount = Exhibitor::where('type', 'adult')->where('has_paid', false)->count();

        $balances = Exhibitor::all()->map->balancePence();
        $totalReceivedPence = Exhibitor::sum('amount_paid_pence');
        $totalDuePence = $balances->filter(fn (int $balance) => $balance > 0)->sum();

        return view('dashboard', compact(
            'sectionCount',
            'classCount',
            'classesWithEntries',
            'classesAwaitingJudging',
            'adultCount',
            'juniorCount',
            'entryCount',
            'resultsEntered',
            'resultsOutstanding',
            'paidCount',
            'unpaidCount',
            'totalReceivedPence',
            'totalDuePence',
        ));
    }
}
