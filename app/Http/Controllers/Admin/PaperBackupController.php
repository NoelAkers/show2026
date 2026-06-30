<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaperBackupController extends Controller
{
    public function __invoke(Request $request): View
    {
        $personnel = User::whereIn('role', [UserRole::Judge, UserRole::Steward])
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($u) => ucfirst($u->role->value).'s');

        $selectedUser = null;
        $sections = collect();
        $trophies = collect();

        if ($userId = $request->query('user_id')) {
            $selectedUser = User::whereIn('role', [UserRole::Judge, UserRole::Steward])
                ->findOrFail($userId);

            $sections = $selectedUser
                ->assignedSections()
                ->with([
                    'showClasses' => fn ($q) => $q->ordered(),
                    'showClasses.entries' => fn ($q) => $q->orderBy('entry_number'),
                ])
                ->ordered()
                ->get();

            $trophies = Trophy::where('judge_id', $selectedUser->id)
                ->orWhere('steward_id', $selectedUser->id)
                ->orderBy('name')
                ->get();
        }

        return view('admin.paper-backup.index', compact('personnel', 'selectedUser', 'sections', 'trophies'));
    }
}
