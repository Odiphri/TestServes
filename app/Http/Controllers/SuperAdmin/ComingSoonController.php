<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class ComingSoonController extends Controller
{
    public function __invoke(string $section)
    {
        $titles = [
            'school-owners' => 'School Owners',
            'payments' => 'Payments',
            'demo-requests' => 'Demo Requests',
            'support-tickets' => 'Support Tickets',
            'activity-logs' => 'Activity Logs',
            'system-settings' => 'System Settings',
            'admin-users' => 'Admin Users',
        ];

        return view('super-admin.coming-soon', [
            'title' => $titles[$section] ?? 'Coming Soon',
        ]);
    }
}
