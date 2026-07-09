<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        return view('owner.dashboard', $this->pageData());
    }

    public function profile()
    {
        return view('owner.profile', $this->pageData());
    }

    public function school()
    {
        return view('owner.school', $this->pageData());
    }

    public function branding()
    {
        return view('owner.branding', $this->pageData());
    }

    public function plans()
    {
        return view('owner.plans', $this->pageData());
    }

    private function pageData(): array
    {
        $owner = Auth::guard('school_owner')->user();
        $owner->load(['school.plan', 'school.branding', 'school.subscriptions', 'school.payments']);
        $school = $owner->school;

        return [
            'owner' => $owner,
            'school' => $school,
            'branding' => $school?->branding,
            'subscription' => $school?->subscriptions()->latest()->first(),
            'payments' => $school?->payments()->latest()->take(5)->get() ?? collect(),
            'plans' => $this->availablePlans(),
        ];
    }

    private function availablePlans()
    {
        return Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get()
            : collect();
    }
}
