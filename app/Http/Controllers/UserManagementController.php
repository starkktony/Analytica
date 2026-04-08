<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('user_management.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                'ends_with:@clsu.edu.ph',
                Rule::unique('users', 'email'),
            ],
            'role'     => ['required', 'string', Rule::in($this->validRoles())],
            'password' => 'required|min:6',
        ]);

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
        $user = User::findOrFail($id);

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
            // role intentionally excluded — locked after creation
        ]);

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully!');
    }

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