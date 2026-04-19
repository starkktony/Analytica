<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserManagementController extends Controller
{
    // Fetch all users and pass to index view
    public function index()
    {
        $users = User::all();
        return view('user_management.index', compact('users'));
    }

    public function store(Request $request)
    {
        // Validate name, institutional email, role, and password
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                'ends_with:@clsu.edu.ph',   // Restrict to institutional domain
                Rule::unique('users', 'email'),
            ],
            'role'     => ['required', 'string', Rule::in($this->validRoles())],
            'password' => 'required|min:6',
        ]);

        // Create user with hashed password
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User added successfully!');
    }

    public function update(Request $request, $id)
    {
        // Abort if user not found
        $user = User::findOrFail($id);

        // Validate name and email, ignoring current user's email uniqueness
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'ends_with:@clsu.edu.ph',
                Rule::unique('users', 'email')->ignore($id),
            ],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            // Role intentionally excluded — locked after creation
        ]);

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully!');
    }

    // Returns list of all allowed user roles
    private function validRoles(): array
    {
        return [
            'Admin',
            'Executive',
            'Director',
            'Chief',
            'Employee-Teaching',
            'Employee-Non-Teaching',
        ];
    }
}