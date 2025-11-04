<?php

namespace App\Http\Controllers;

use IlluminateAgnostic\Arr;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        // Get all users and load their roles
        $users = User::with('roles')->latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Get all roles
        $roles = Role::all();
        // Get the names of the roles this user has
        $userRoles = $user->roles->pluck('name')->all();

        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user's roles in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validate the input
        $validated = $request->validate([
            'roles' => 'nullable|array' // 'roles' must be an array (even if empty)
        ]);

        // 2. Sync the roles
        // This command detaches any old roles and attaches only the new ones.
        $user->syncRoles($validated['roles'] ?? []);

        // 3. Redirect back
        return redirect()->route('users.index')->with('success', 'User roles updated successfully.');
    }
}