<?php

namespace App\Http\Controllers\Exhibitor;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExhibitorController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $exhibitor = $user->exhibitor;

        if (! $exhibitor) {
            Exhibitor::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'type' => 'adult',
            ]);

            return redirect()->route('exhibitor.profile.edit')
                ->with('info', 'Welcome! Please complete your exhibitor profile.');
        }

        $exhibitor->load(['entries.showClass.showSection', 'entries.result']);

        return view('exhibitor.dashboard', compact('exhibitor'));
    }

    public function edit(Request $request): View
    {
        $exhibitor = $request->user()->exhibitor;

        abort_unless($exhibitor, 404);

        return view('exhibitor.edit', compact('exhibitor'));
    }

    public function update(Request $request): RedirectResponse
    {
        $exhibitor = $request->user()->exhibitor;

        abort_unless($exhibitor, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'sort_name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['adult', 'junior'])],
            'is_resident' => ['boolean'],
            'is_novice' => ['boolean'],
        ]);

        $exhibitor->update($validated);

        return redirect()->route('exhibitor.dashboard')
            ->with('success', 'Your profile has been updated.');
    }
}
