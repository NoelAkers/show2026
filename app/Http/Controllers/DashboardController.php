<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\Transaction;
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
        $adultExhibitors = Exhibitor::where('type', 'adult')->get();
        $paidCount = $adultExhibitors->filter->hasPaid()->count();
        $unpaidCount = $adultExhibitors->count() - $paidCount;

        $balances = Exhibitor::all()->map->balancePence();
        $totalReceivedPence = Transaction::whereIn('type', [TransactionType::CashReceipt->value, TransactionType::CardPayment->value])->sum('amount_pence')
            - Transaction::where('type', TransactionType::CashPayment->value)->sum('amount_pence');
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
