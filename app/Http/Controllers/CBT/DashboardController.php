<?php

namespace App\Http\Controllers\CBT;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('cbt.dashboard');
    }
}
