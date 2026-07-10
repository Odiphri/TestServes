<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load(['profile', 'assignedClass', 'subjects']);

        return view('student.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
                'password_changed_at' => now(),
            ]);
        }

        $profileData = collect($validated)->only(['phone', 'address', 'bio'])->toArray();
        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        if ($request->hasFile('profile_picture')) {
            $profile->updateProfilePicture($request->file('profile_picture'));
        }

        return back()->with('success', 'Profile updated.');
    }
}
