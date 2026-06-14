<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->validated(),
            'password' => Str::password(16),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} added.");
    }

    public function edit(User $user, Request $request): View
    {
        $isSelf = $request->user()->id === $user->id;

        return view('admin.users.edit', compact('user', 'isSelf'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->only(['name', 'email', 'phone']);

        if ($request->user()->id !== $user->id) {
            $data['role'] = $request->validated('role');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} updated.");
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
