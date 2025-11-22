<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use App\Models\StockLocation;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        // Start query
        $query = User::with('roles')->latest();
        
        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
        });
        
        // [MODIFIED] Paginate the query
        $users = $query->paginate(20)->appends($request->query());
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->syncRoles($validated['roles'] ?? []);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating user: ' + $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->all();
        
        // Fetch Active Stock Locations
        $stockLocations = StockLocation::where('is_active', true)->orderBy('name')->get();
        $userLocationIds = $user->stockLocations->pluck('id')->all();

        return view('users.edit', compact('user', 'roles', 'userRoles', 'stockLocations', 'userLocationIds'));
    }

    /**
     * Update the specified user's roles in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class. ',email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'nullable|array',
            'stock_locations' => 'nullable|array', // Validate stock locations
            'stock_locations.*' => 'exists:stock_locations,id',
        ]);

        DB::beginTransaction();
        try {
            // Update user details
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            // Only update password if one was provided
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            // Sync roles
            $user->syncRoles($validated['roles'] ?? []);

            // Sync Stock Locations
            $user->stockLocations()->sync($validated['stock_locations'] ?? []);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating user: ' + $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Don't let an admin delete themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Optional: Don't let anyone delete the original Admin user
        if ($user->email === 'admin@mail.com') {
             return back()->with('error', 'You cannot delete the default admin user.');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}