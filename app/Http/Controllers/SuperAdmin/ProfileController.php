<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('super-admin.profile.edit', [
            'admin' => Auth::guard('platform_admin')->user(),
        ]);
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('platform_admins', 'email')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_picture' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (filled($data['new_password'] ?? null)) {
            if (! Hash::check($data['current_password'] ?? '', $admin->password)) {
                return back()
                    ->withErrors(['current_password' => 'The current password is incorrect.'])
                    ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            $data['password'] = Hash::make($data['new_password']);
        }

        if ($request->boolean('remove_profile_picture') && $admin->profile_picture) {
            Storage::disk('public')->delete($admin->profile_picture);
            $data['profile_picture'] = null;
        } elseif ($request->hasFile('profile_picture')) {
            if ($admin->profile_picture) {
                Storage::disk('public')->delete($admin->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile-pictures/admins', 'public');
        } else {
            unset($data['profile_picture']);
        }

        unset($data['remove_profile_picture'], $data['current_password'], $data['new_password'], $data['new_password_confirmation']);
        $admin->update($data);
        PlatformActivity::log('admin_profile_updated', "Updated own admin profile {$admin->email}.", $admin);

        return back()->with('success', 'Your admin profile has been updated.');
    }
}
