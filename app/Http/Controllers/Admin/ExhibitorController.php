<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExhibitorRequest;
use App\Http\Requests\Admin\UpdateExhibitorRequest;
use App\Models\Exhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorController extends Controller
{
    public function index(Request $request): View
    {
        $exhibitors = Exhibitor::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('is_resident'), fn ($q) => $q->where('is_resident', (bool) $request->is_resident))
            ->when($request->filled('has_paid'), fn ($q) => $q->where('has_paid', (bool) $request->has_paid))
            ->orderBy('sort_name')
            ->get();

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

    public function markPaid(Exhibitor $exhibitor): RedirectResponse
    {
        $exhibitor->update(['has_paid' => true]);

        return back()->with('success', "{$exhibitor->full_name} marked as paid.");
    }

    public function markUnpaid(Exhibitor $exhibitor): RedirectResponse
    {
        $exhibitor->update(['has_paid' => false]);

        return back()->with('success', "{$exhibitor->full_name} marked as unpaid.");
    }
}
