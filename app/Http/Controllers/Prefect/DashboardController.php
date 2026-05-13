<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('prefect.dashboard');
    }
}
