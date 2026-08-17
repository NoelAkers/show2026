<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePrizeLevelRequest;
use App\Http\Requests\Admin\UpdatePrizeLevelRequest;
use App\Models\PrizeLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrizeLevelController extends Controller
{
    public function index(): View
    {
        $prizeLevels = PrizeLevel::withCount('showClasses')->get();

        return view('admin.prize-levels.index', compact('prizeLevels'));
    }

    public function create(): View
    {
        return view('admin.prize-levels.create');
    }

    public function store(StorePrizeLevelRequest $request): RedirectResponse
    {
        PrizeLevel::create($request->validated());

        return redirect()->route('admin.prize-levels.index')
            ->with('success', 'Prize level created.');
    }

    public function edit(PrizeLevel $prizeLevel): View
    {
        return view('admin.prize-levels.edit', compact('prizeLevel'));
    }

    public function update(UpdatePrizeLevelRequest $request, PrizeLevel $prizeLevel): RedirectResponse
    {
        $prizeLevel->update($request->validated());

        return redirect()->route('admin.prize-levels.index')
            ->with('success', 'Prize level updated.');
    }

    public function destroy(PrizeLevel $prizeLevel): RedirectResponse
    {
        if ($prizeLevel->showClasses()->exists()) {
            return back()->with('error', 'Cannot delete a prize level that is assigned to classes.');
        }

        $prizeLevel->delete();

        return redirect()->route('admin.prize-levels.index')
            ->with('success', 'Prize level deleted.');
    }
}
