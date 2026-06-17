<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStewardRequest;
use App\Http\Requests\Admin\UpdateStewardRequest;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StewardController extends Controller
{
    public function index(): View
    {
        $stewards = User::where('role', UserRole::Steward)
            ->withCount(['assignedSections'])
            ->orderBy('name')
            ->get();

        return view('admin.stewards.index', compact('stewards'));
    }

    public function create(): View
    {
        $sections = ShowSection::ordered()->get();

        return view('admin.stewards.create', compact('sections'));
    }

    public function store(StoreStewardRequest $request): RedirectResponse
    {
        $steward = User::firstOrCreate(
            ['email' => $request->validated('email')],
            [
                'name' => $request->validated('name'),
                'phone' => $request->validated('phone'),
                'role' => UserRole::Steward,
                'password' => Str::password(16),
            ]
        );

        $steward->assignedSections()->sync($request->validated('section_ids', []));

        return redirect()->route('admin.stewards.index')
            ->with('success', "{$steward->name} added as a steward.");
    }

    public function edit(User $steward): View
    {
        $sections = ShowSection::ordered()->get();

        return view('admin.stewards.edit', compact('steward', 'sections'));
    }

    public function update(UpdateStewardRequest $request, User $steward): RedirectResponse
    {
        $steward->update($request->only(['name', 'email', 'phone']));
        $steward->assignedSections()->sync($request->validated('section_ids', []));

        return redirect()->route('admin.stewards.index')
            ->with('success', "{$steward->name} updated.");
    }

    public function destroy(User $steward): RedirectResponse
    {
        $steward->assignedSections()->detach();

        if (! $steward->isAdmin()) {
            $steward->delete();
        }

        return redirect()->route('admin.stewards.index')
            ->with('success', 'Steward removed.');
    }
}
