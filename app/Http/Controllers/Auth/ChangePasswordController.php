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

        return redirect()->route($this->redirectRouteFor($user))->with('success', 'Password changed successfully!');
    }

    private function redirectRouteFor($user): string
    {
        if ($user->can('bursary.manage')) {
            return $this->bursaryRouteFor($user->role);
        }

        return $this->dashboardRouteFor($user->role);
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

    private function bursaryRouteFor(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.payments',
            'hod' => 'hod.payments',
            'cbt_personnel' => 'cbt.payments',
            'teacher' => 'teacher.payments',
            default => 'home',
        };
    }
}
