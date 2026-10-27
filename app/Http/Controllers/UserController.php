<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->paginate(15),
            'roles' => User::ROLE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        abort_if($request->user()->is($user), 403, 'You cannot change your own role.');

        $cannotDemoteLastSuperAdmin = DB::transaction(function () use ($user, $validated): bool {
            $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)
                ->lockForUpdate()
                ->count();

            if ($user->isSuperAdmin()
                && $validated['role'] !== User::ROLE_SUPER_ADMIN
                && $superAdminCount <= 1) {
                return true;
            }

            $user->update(['role' => $validated['role']]);

            return false;
        });

        if ($cannotDemoteLastSuperAdmin) {
            return back()->withErrors(['role' => 'The last super admin cannot be demoted.']);
        }

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }
}
