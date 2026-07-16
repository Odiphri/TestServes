<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Support\SubscriptionLifecycleService;
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
        $school = $school ? app(SubscriptionLifecycleService::class)->refresh($school) : null;

        return [
            'owner' => $owner,
            'school' => $school,
            'branding' => $school?->branding,
            'subscription' => $school?->subscriptions()->latest()->first(),
            'payments' => $school?->payments()->latest()->take(5)->get() ?? collect(),
            'plans' => $this->availablePlans(),
            'lifecycle' => $this->lifecycleSummary($school),
        ];
    }

    private function lifecycleSummary($school): array
    {
        if (! $school) {
            return [];
        }

        $dueAt = $school->next_payment_due_at ?: ($school->payment_status === 'trial' ? $school->trial_ends_at : $school->subscription_ends_at) ?: $school->subscription_expires_at;
        $deactivationAt = $school->deactivation_scheduled_at ?: ($school->payment_grace_ends_at?->copy()->endOfDay());

        return [
            'due_at' => $dueAt,
            'deactivation_at' => $deactivationAt,
            'days_until_due' => $dueAt ? (int) now()->startOfDay()->diffInDays($dueAt->copy()->startOfDay(), false) : null,
            'days_until_deactivation' => $deactivationAt ? (int) now()->startOfDay()->diffInDays($deactivationAt->copy()->startOfDay(), false) : null,
            'has_paid_before' => $school->payments()->where('status', 'paid')->exists(),
        ];
    }

    private function availablePlans()
    {
        return Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get()
            : collect();
    }
}
