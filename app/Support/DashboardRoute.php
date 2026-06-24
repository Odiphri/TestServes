<?php

namespace App\Support;

use App\Models\User;

class DashboardRoute
{
    public static function forUser(User $user): string
    {
        return match ($user->role) {
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
