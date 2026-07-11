<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\SchoolSubscription;
use App\Models\ActivityLog;
use App\Models\PaymentRecord;
use App\Models\SupportTicket;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_schools' => $this->countSchools(),
            'active_schools' => $this->countSchools('active'),
            'pending_schools' => $this->countSchools('pending'),
            'suspended_schools' => $this->countSchools('suspended'),
            'trial_schools' => $this->countSchools('trial'),
            'expired_schools' => $this->countSchools('expired'),
            'deleted_schools' => Schema::hasTable('schools') ? School::onlyTrashed()->count() : 0,
            'total_school_owners' => Schema::hasTable('school_owners') ? SchoolOwner::count() : 0,
            'total_subscription_plans' => Schema::hasTable('subscription_plans') ? SubscriptionPlan::count() : 0,
            'total_payments' => Schema::hasTable('payment_records') ? PaymentRecord::count() : 0,
            'pending_payments' => Schema::hasTable('payment_records') ? PaymentRecord::where('status', 'pending')->count() : 0,
            'confirmed_payments' => Schema::hasTable('payment_records') ? PaymentRecord::where('status', 'paid')->count() : 0,
            'monthly_revenue' => Schema::hasTable('payment_records')
                ? PaymentRecord::where('status', 'paid')->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount')
                : 0,
            'yearly_revenue' => Schema::hasTable('payment_records')
                ? PaymentRecord::where('status', 'paid')->whereYear('payment_date', now()->year)->sum('amount')
                : 0,
            'expiring_subscriptions' => Schema::hasTable('schools')
                ? School::whereDate('subscription_expires_at', '>=', now())->whereDate('subscription_expires_at', '<=', now()->addDays(14))->count()
                : 0,
        ];

        $recentSchools = Schema::hasTable('schools')
            ? School::with(['owner', 'plan'])->latest()->limit(6)->get()
            : collect();

        $recentPayments = Schema::hasTable('payment_records')
            ? PaymentRecord::with(['school', 'plan'])->latest()->limit(6)->get()
            : collect();

        $recentOwners = Schema::hasTable('school_owners') ? SchoolOwner::with('school')->latest()->limit(6)->get() : collect();
        $recentLogs = Schema::hasTable('activity_logs') ? ActivityLog::with('actor')->latest()->limit(6)->get() : collect();
        $recentSupportTickets = Schema::hasTable('support_tickets') ? SupportTicket::with('school')->latest()->limit(6)->get() : collect();

        return view('super-admin.dashboard', compact('stats', 'recentSchools', 'recentPayments', 'recentOwners', 'recentLogs', 'recentSupportTickets'));
    }

    private function countSchools(?string $status = null): int
    {
        if (! Schema::hasTable('schools')) {
            return 0;
        }

        return School::when($status, fn ($query) => $query->where('status', $status))->count();
    }
}
