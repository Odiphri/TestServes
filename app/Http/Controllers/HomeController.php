<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Send authenticated users to the dashboard for their portal role.
     */
    public function index()
    {
        $user = auth()->user();

        return match ($user?->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'hod' => redirect()->route('hod.dashboard'),
            'cbt_personnel' => redirect()->route('cbt.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'prefect' => redirect()->route('prefect.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect('/login'),
        };
    }
}
