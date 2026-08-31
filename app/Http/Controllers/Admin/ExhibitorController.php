<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExhibitorEntryRequest;
use App\Http\Requests\Admin\StoreExhibitorRequest;
use App\Http\Requests\Admin\StoreTransactionRequest;
use App\Http\Requests\Admin\UpdateExhibitorRequest;
use App\Models\Entry;
use App\Models\Exhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorController extends Controller
{
    public function index(Request $request): View
    {
        $exhibitors = Exhibitor::query()
            ->when($request->filled('search'), fn ($q) => $q->where('full_name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('is_resident'), fn ($q) => $q->where('is_resident', (bool) $request->is_resident))
            ->orderBy('sort_name')
            ->get()
            ->when(
                $request->filled('has_paid'),
                fn ($exhibitors) => $exhibitors->filter(fn (Exhibitor $exhibitor) => $exhibitor->hasPaid() === (bool) $request->has_paid)->values()
            );

        return view('admin.exhibitors.index', compact('exhibitors'));
    }

    public function create(): View
    {
        return view('admin.exhibitors.create');
    }

    public function store(StoreExhibitorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['full_name'] = $data['first_name'].' '.$data['last_name'];
        $data['sort_name'] = $data['last_name'].', '.$data['first_name'];

        Exhibitor::create($data);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor created.');
    }

    public function show(Exhibitor $exhibitor): View
    {
        $exhibitor->load('entries.result');

        return view('admin.exhibitors.show', compact('exhibitor'));
    }

    public function edit(Exhibitor $exhibitor): View
    {
        return view('admin.exhibitors.edit', compact('exhibitor'));
    }

    public function update(UpdateExhibitorRequest $request, Exhibitor $exhibitor): RedirectResponse
    {
        $data = $request->validated();
        $data['full_name'] = $data['first_name'].' '.$data['last_name'];
        $data['sort_name'] = $data['last_name'].', '.$data['first_name'];

        $exhibitor->update($data);

        return redirect()->route('admin.exhibitors.show', $exhibitor)
            ->with('success', 'Exhibitor updated.');
    }

    public function destroy(Exhibitor $exhibitor): RedirectResponse
    {
        if ($exhibitor->entries()->exists()) {
            return back()->with('error', 'Cannot delete an exhibitor who has entries.');
        }

        $exhibitor->delete();

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor deleted.');
    }

    public function addEntry(Exhibitor $exhibitor): View
    {
        return view('admin.exhibitors.add-entry', compact('exhibitor'));
    }

    public function storeEntry(StoreExhibitorEntryRequest $request, Exhibitor $exhibitor): RedirectResponse
    {
        $classId = $request->validated('show_class_id');
        $quantity = $request->validated('quantity');

        $newEntries = collect();
        for ($i = 0; $i < $quantity; $i++) {
            $newEntries->push(Entry::create([
                'show_class_id' => $classId,
                'exhibitor_id' => $exhibitor->id,
            ]));
        }

        $message = $quantity === 1 ? 'Entry added.' : "{$quantity} entries added.";
        $labelUrl = route('admin.exhibitors.labels', $exhibitor).'?'.http_build_query(['entries' => $newEntries->pluck('id')->all()]);

        return redirect($labelUrl)->with('success', $message);
    }

    public function labels(Request $request, Exhibitor $exhibitor): View
    {
        $entryIds = array_filter((array) $request->query('entries', []));

        $entries = $exhibitor->entries()
            ->with(['showClass.showSection'])
            ->when(! empty($entryIds), fn ($q) => $q->whereIn('id', $entryIds))
            ->orderBy('entry_number')
            ->get();

        return view('admin.exhibitors.labels', compact('exhibitor', 'entries'));
    }

    public function storeTransaction(StoreTransactionRequest $request, Exhibitor $exhibitor): RedirectResponse
    {
        $type = TransactionType::from($request->validated('type'));
        $amountPounds = $request->validated('amount_pounds');

        $amountPence = $amountPounds !== null
            ? (int) round($amountPounds * 100)
            : match ($type) {
                TransactionType::CashReceipt, TransactionType::CardPayment => $exhibitor->outstandingFromExhibitorPence(),
                TransactionType::CashPayment => $exhibitor->owedToExhibitorPence(),
            };

        if ($amountPence <= 0) {
            return back()->with('error', 'No amount is due, so no transaction was recorded.');
        }

        $exhibitor->transactions()->create([
            'amount_pence' => $amountPence,
            'type' => $type,
        ]);

        return back()->with('success', 'Transaction recorded.');
    }
}
