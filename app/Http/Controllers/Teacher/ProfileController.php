<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load('profile');
        $routePrefix = $this->routePrefix();

        return view('teacher.profile.edit', compact('user', 'routePrefix'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'portal_id' => 'required|string|max:255|unique:users,portal_id,' . $user->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
            $validated['password_changed_at'] = now();
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated.');
    }

    private function routePrefix(): string
    {
        $role = Auth::user()->role;

        return $role === 'cbt_personnel' ? 'cbt' : $role;
    }
}
