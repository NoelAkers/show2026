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
            $parts = explode(' ', trim($user->name));
            $lastName = array_pop($parts);
            $firstName = implode(' ', $parts);

            Exhibitor::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'sort_name' => $firstName !== '' ? "{$lastName}, {$firstName}" : $lastName,
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

        $request->merge([
            'is_resident' => $request->boolean('is_resident'),
            'is_novice' => $request->boolean('is_novice'),
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email:filter', 'max:255'],
            'type' => ['required', Rule::in(['adult', 'junior'])],
            'is_resident' => ['boolean'],
            'is_novice' => ['boolean'],
        ]);

        $validated['sort_name'] = $validated['first_name'] !== ''
            ? "{$validated['last_name']}, {$validated['first_name']}"
            : $validated['last_name'];

        $exhibitor->update($validated);

        return redirect()->route('exhibitor.dashboard')
            ->with('success', 'Your profile has been updated.');
    }
}
