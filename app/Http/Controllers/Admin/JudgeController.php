<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJudgeRequest;
use App\Http\Requests\Admin\UpdateJudgeRequest;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JudgeController extends Controller
{
    public function index(): View
    {
        $judges = User::where('is_judge', true)
            ->withCount(['assignedSections'])
            ->orderBy('name')
            ->get();

        return view('admin.judges.index', compact('judges'));
    }

    public function create(): View
    {
        $sections = ShowSection::ordered()->get();

        return view('admin.judges.create', compact('sections'));
    }

    public function store(StoreJudgeRequest $request): RedirectResponse
    {
        $judge = User::firstOrCreate(
            ['email' => $request->validated('email')],
            [
                'name' => $request->validated('name'),
                'phone' => $request->validated('phone'),
                'role' => 'judge',
                'is_judge' => true,
                'password' => Str::password(16),
            ]
        );

        $judge->update(['is_judge' => true]);
        $judge->assignedSections()->sync($request->validated('section_ids', []));

        return redirect()->route('admin.judges.index')
            ->with('success', "{$judge->name} added as a judge.");
    }

    public function edit(User $judge): View
    {
        $sections = ShowSection::ordered()->get();

        return view('admin.judges.edit', compact('judge', 'sections'));
    }

    public function update(UpdateJudgeRequest $request, User $judge): RedirectResponse
    {
        $judge->update($request->only(['name', 'email', 'phone']));
        $judge->assignedSections()->sync($request->validated('section_ids', []));

        return redirect()->route('admin.judges.index')
            ->with('success', "{$judge->name} updated.");
    }

    public function destroy(User $judge): RedirectResponse
    {
        $judge->assignedSections()->detach();

        if ($judge->isAdmin()) {
            $judge->update(['is_judge' => false]);
        } else {
            $judge->delete();
        }

        return redirect()->route('admin.judges.index')
            ->with('success', 'Judge removed.');
    }
}
