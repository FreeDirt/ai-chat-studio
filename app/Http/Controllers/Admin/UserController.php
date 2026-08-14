<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display list of team members.
     */
    public function index()
    {
        $users = User::withCount('conversations')->latest()->get();

        $stats = [
            'total'       => $users->count(),
            'admins'      => $users->where('role', 'super_admin')->count(),
            'members'     => $users->where('role', 'member')->count(),
            'deactivated' => $users->where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Store new team member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:super_admin,member',
            'token_quota' => 'nullable|integer|min:0',
        ]);

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'is_active'   => true,
            'token_quota' => $request->token_quota ?: null,
        ]);

        return redirect()->route('admin.users.index')->with('toast', [
            'type'    => 'success',
            'message' => 'User ' . $request->name . ' created successfully!',
        ]);
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'role'        => 'required|in:super_admin,member',
            'token_quota' => 'nullable|integer|min:0',
            'password'    => 'nullable|string|min:8',
        ]);

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'role'        => $request->role,
            'token_quota' => $request->token_quota ?: null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('toast', [
            'type'    => 'success',
            'message' => 'User ' . $user->name . ' updated!',
        ]);
    }

    /**
     * Toggle user role (Promote / Demote Super Admin).
     */
    public function toggleRole(User $user)
    {
        // Prevent demoting self if only 1 super admin left
        if ($user->id === auth()->id() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Cannot demote yourself. At least one Super Admin must exist.',
            ]);
        }

        $newRole = $user->role === 'super_admin' ? 'member' : 'super_admin';
        $user->update(['role' => $newRole]);

        $label = $newRole === 'super_admin' ? 'promoted to Super Admin' : 'changed to Member';
        return back()->with('toast', [
            'type'    => 'success',
            'message' => "{$user->name} was {$label}!",
        ]);
    }

    /**
     * Toggle active status (Enable / Disable).
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You cannot deactivate your own logged-in account.',
            ]);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('toast', [
            'type'    => 'info',
            'message' => "User account for {$user->name} was {$status}.",
        ]);
    }

    /**
     * Delete user account.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You cannot delete your own account.',
            ]);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('toast', [
            'type'    => 'success',
            'message' => "User {$name} was deleted.",
        ]);
    }
}
