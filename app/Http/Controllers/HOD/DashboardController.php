<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('hod.dashboard');
    }
}
