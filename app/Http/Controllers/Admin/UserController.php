<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // Show list
    public function index(): Response
    {
        $users = User::all();

        return Inertia::render('admin/User', [
            'users' => $users,
        ]);
    }

    // create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'name' => explode('@', $validated['email'])[0], // Use email prefix as name
            ]);

            return redirect()->back()->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create user. Please try again.');
        }
    }
    // delete
    public function destroy($id): RedirectResponse
    {

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }
}
