<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function change(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route($this->dashboardRouteFor($user->role))->with('success', 'Password changed successfully!');
    }

    private function dashboardRouteFor(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'hod' => 'hod.dashboard',
            'cbt_personnel' => 'cbt.dashboard',
            'teacher' => 'teacher.dashboard',
            'prefect' => 'prefect.dashboard',
            'student' => 'student.dashboard',
            default => 'home',
        };
    }
}
