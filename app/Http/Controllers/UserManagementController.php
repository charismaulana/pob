<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index(Request $request)
    {
        // Only super admin can access
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $status = $request->get('status', 'all');

        $query = User::query()->where('id', '!=', auth()->id());

        if ($status === 'pending') {
            $query->pendingApproval();
        } elseif ($status === 'approved') {
            $query->approved();
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => User::count(),
            'pending' => User::pendingApproval()->count(),
            'approved' => User::approved()->count(),
        ];

        return view('admin.users', compact('users', 'stats', 'status'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $user->update(['is_approved' => true]);

        return back()->with('success', "User {$user->name} telah disetujui.");
    }

    /**
     * Revoke user approval.
     */
    public function revoke(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Tidak dapat mencabut persetujuan Super Admin.');
        }

        $user->update(['is_approved' => false]);

        return back()->with('success', "Persetujuan user {$user->name} telah dicabut.");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "User {$userName} telah dihapus.");
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'role' => 'required|in:superadmin,gs,department_user',
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Role {$user->name} telah diubah menjadi {$validated['role']}.");
    }
}
