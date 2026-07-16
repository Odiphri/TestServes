<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\SystemSetting;
use App\Support\PlatformActivity;
use App\Support\SchoolLifecycle;
use App\Support\TenantDatabaseManager;
use App\Support\TestServesDomains;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;

class SchoolController extends Controller
{
    use AuthorizesPlatformSections;

    public function index(Request $request)
    {
        $schools = School::with(['owner', 'plan', 'branding'])
            ->when($request->boolean('archived'), fn ($query) => $query->onlyTrashed())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('plan'), fn ($query) => $query->where('subscription_plan_id', $request->plan))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('super-admin.schools.index', [
            'schools' => $schools,
            'plans' => SubscriptionPlan::orderBy('name')->get(),
            'canManage' => $this->isSuperAdmin(),
        ]);
    }

    public function create()
    {
        $this->requireSuperAdmin();

        return view('super-admin.schools.create', [
            'school' => new School(),
            'plans' => SubscriptionPlan::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->requireSuperAdmin();
        $data = $this->validated($request);
        $logoPath = $this->storeLogo($request);

        $school = DB::transaction(function () use ($data, $logoPath) {
            $school = School::create($this->schoolPayload($data));

            $school->owner()->create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'] ?? null,
                'phone' => $data['owner_phone'] ?? null,
                'is_primary' => true,
            ]);

            $school->branding()->create($this->brandingPayload($data, $logoPath));

            $school->subscriptions()->create([
                'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
                'starts_at' => $data['subscription_starts_at'] ?? null,
                'expires_at' => $data['subscription_expires_at'] ?? null,
                'status' => in_array($data['status'], ['active', 'trial'], true) ? $data['status'] : 'pending',
            ]);
            PlatformActivity::log('school_created', "Created school {$school->name}.", $school);

            return $school;
        });

        if (in_array($school->status, ['active', 'trial'], true)) {
            app(TenantDatabaseManager::class)->createAndMigrate($school);
        }

        return redirect()->route('super-admin.schools.index')->with('success', 'School created successfully.');
    }

    public function show(School $school)
    {
        $school->load(['owner', 'plan', 'branding', 'subscriptions.plan']);

        return view('super-admin.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        $this->requireSuperAdmin();
        $school->load(['owner', 'branding']);

        return view('super-admin.schools.edit', [
            'school' => $school,
            'plans' => SubscriptionPlan::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, School $school)
    {
        $this->requireSuperAdmin();
        $data = $this->validated($request, $school);
        $logoPath = $this->storeLogo($request);

        DB::transaction(function () use ($school, $data, $logoPath) {
            $school->update($this->schoolPayload($data));

            $school->owner()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name' => $data['owner_name'],
                    'email' => $data['owner_email'] ?? null,
                    'phone' => $data['owner_phone'] ?? null,
                    'is_primary' => true,
                ]
            );

            $branding = $this->brandingPayload($data, $logoPath ?: $school->branding?->logo_path);
            $school->branding()->updateOrCreate(['school_id' => $school->id], $branding);
            PlatformActivity::log('school_updated', "Updated school {$school->name}.", $school);
        });

        return redirect()->route('super-admin.schools.show', $school)->with('success', 'School settings updated.');
    }

    public function destroy(School $school)
    {
        $this->requireSuperAdmin();

        $school->delete();

        PlatformActivity::log('school_deleted', "Deleted school {$school->name}.", $school);

        return redirect()->route('super-admin.schools.index')->with('success', 'School deleted. You can restore it from Archived schools.');
    }

    public function updateStatus(School $school, string $status, SchoolLifecycle $lifecycle)
    {
        $this->requirePlatformPermission('schools.lifecycle');

        $targetStatus = SchoolLifecycle::normalize($status);
        abort_unless(in_array($targetStatus, SchoolLifecycle::statuses(), true), 404);

        $extra = [];
        $reason = request('reason') ?: request('deactivation_reason');
        $sessionVersion = (int) ($school->portal_session_version ?: 1) + 1;

        if (in_array($targetStatus, [SchoolLifecycle::SUSPENDED, SchoolLifecycle::DEACTIVATED, SchoolLifecycle::ARCHIVED], true)) {
            $settings = SystemSetting::values();
            $noticeDays = max(1, (int) ($settings['deactivated_school_delete_after_days'] ?? 30));
            $extra += [
                'deactivation_reason' => request('deactivation_reason') ?: 'The school was deactivated by TestServes administration.',
                'deactivated_at' => now(),
                'deactivation_scheduled_at' => now(),
                'delete_scheduled_at' => now()->addDays($noticeDays),
                'portal_locked' => true,
                'portal_session_version' => $sessionVersion,
            ];
        }

        if ($targetStatus === SchoolLifecycle::SUSPENDED) {
            $suspensionReason = request('suspension_reason') ?: request('deactivation_reason') ?: 'The school was suspended by TestServes administration.';
            $extra += [
                'suspension_reason' => $suspensionReason,
                'deactivation_reason' => $suspensionReason,
                'suspended_at' => now(),
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
            ];
            $reason = $reason ?: $suspensionReason;
        }

        if ($targetStatus === SchoolLifecycle::EXPIRED) {
            $extra += [
                'expired_at' => now(),
                'portal_locked' => true,
                'portal_session_version' => $sessionVersion,
            ];
        }

        if ($targetStatus === SchoolLifecycle::EXPIRED && $school->status === SchoolLifecycle::TRIAL) {
            $extra += [
                'trial_ends_at' => now(),
                'trial_ended_at' => now(),
                'subscription_expires_at' => now()->toDateString(),
                'next_payment_due_at' => now()->toDateString(),
                'payment_grace_ends_at' => now()->toDateString(),
                'deactivation_scheduled_at' => now(),
                'deactivation_reason' => request('deactivation_reason') ?: 'The free trial was ended by TestServes administration. Please renew to restore the portal.',
                'portal_locked' => true,
            ];
            $reason = $reason ?: 'Trial ended by TestServes administration.';
        }

        if ($targetStatus === SchoolLifecycle::TRIAL) {
            $settings = SystemSetting::values();
            $trialDays = (int) ($school->plan?->trial_days ?: ($settings['default_trial_days'] ?? 14));
            $trialEndsAt = now()->addDays(max(1, $trialDays));

            $extra += [
                'subscription_starts_at' => now()->toDateString(),
                'subscription_expires_at' => $trialEndsAt->toDateString(),
                'trial_ends_at' => $trialEndsAt,
                'subscription_ends_at' => null,
                'next_payment_due_at' => $trialEndsAt->toDateString(),
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'deactivation_reason' => null,
                'portal_locked' => false,
                'portal_session_version' => $sessionVersion,
                'expired_at' => null,
                'suspended_at' => null,
                'deactivated_at' => null,
                'trial_ended_at' => null,
                'suspension_reason' => null,
            ];
            $reason = $reason ?: "Trial started for {$trialDays} day(s).";
        }

        if ($targetStatus === SchoolLifecycle::ACTIVE) {
            $expiresAt = now()->addMonth()->toDateString();

            $extra += [
                'activated_at' => now(),
                'subscription_starts_at' => now()->toDateString(),
                'subscription_expires_at' => $expiresAt,
                'subscription_ends_at' => $expiresAt,
                'trial_ends_at' => null,
                'next_payment_due_at' => $expiresAt,
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'last_payment_failed_at' => null,
                'deactivation_reason' => null,
                'suspension_reason' => null,
                'expired_at' => null,
                'suspended_at' => null,
                'trial_ended_at' => null,
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
                'portal_locked' => false,
                'portal_session_version' => $sessionVersion,
            ];
            $reason = $reason ?: 'Portal access restored by TestServes administration.';
        }

        $updated = $lifecycle->transition($school, $targetStatus, $this->platformAdmin(), $reason, $extra);

        if (in_array($updated->status, ['active', 'trial'], true)) {
            app(TenantDatabaseManager::class)->createAndMigrate($updated);
        }

        return back()->with('success', 'School status updated.');
    }

    public function restore(int $school)
    {
        $this->requireSuperAdmin();
        $restored = School::onlyTrashed()->findOrFail($school);
        $restored->restore();
        $restored->update([
            'status' => SchoolLifecycle::AWAITING_PAYMENT,
            'subscription_status' => 'pending',
            'payment_status' => 'pending',
            'portal_locked' => true,
            'portal_session_version' => (int) ($restored->portal_session_version ?: 1) + 1,
        ]);
        PlatformActivity::log('school_restored', "Restored school {$restored->name}.", $restored);

        return redirect()->route('super-admin.schools.index')->with('success', 'School restored.');
    }

    public function resetOwnerPassword(School $school)
    {
        $this->requireSuperAdmin();
        return back()->with('info', "Password reset for {$school->owner?->email} is prepared for the next phase.");
    }

    private function validated(Request $request, ?School $school = null): array
    {
        $slugRule = Rule::unique('schools', 'slug');

        if ($school) {
            $slugRule->ignore($school->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:120', $slugRule],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'status' => ['required', Rule::in(['pending', 'awaiting_payment', 'active', 'suspended', 'trial', 'expired', 'deactivated'])],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_expires_at' => ['nullable', 'date', 'after_or_equal:subscription_starts_at'],
            'next_payment_due_at' => ['nullable', 'date'],
            'payment_grace_ends_at' => ['nullable', 'date'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'auto_renew' => ['nullable', 'boolean'],
            'portal_locked' => ['nullable', 'boolean'],
            'deactivation_scheduled_at' => ['nullable', 'date'],
            'deactivation_reason' => ['nullable', 'string', 'max:2000'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'short_name' => ['nullable', 'string', 'max:80'],
            'portal_display_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'school_type' => ['nullable', 'string', 'max:120'],
            'expected_students_count' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function schoolPayload(array $data): array
    {
        $slug = Str::slug($data['slug']);
        $subscriptionStatus = in_array($data['status'], ['active', 'trial', 'expired'], true) ? $data['status'] : (in_array($data['status'], ['suspended', 'deactivated'], true) ? 'cancelled' : 'pending');
        $paymentStatus = match ($data['status']) {
            'active' => 'paid',
            'trial' => 'trial',
            'expired' => 'expired',
            'suspended' => 'suspended',
            'deactivated' => 'deactivated',
            default => 'pending',
        };
        $portalLocked = in_array($data['status'], ['pending', 'awaiting_payment', 'expired', 'suspended', 'deactivated'], true) || (bool) ($data['portal_locked'] ?? false);
        $expiresAt = $data['subscription_expires_at'] ?? null;

        return [
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'portal_url' => TestServesDomains::portalUrl($slug),
            'status' => $data['status'],
            'subscription_starts_at' => $data['subscription_starts_at'] ?? null,
            'subscription_expires_at' => $expiresAt,
            'trial_ends_at' => $data['status'] === 'trial' ? $expiresAt : null,
            'subscription_ends_at' => $data['status'] === 'active' ? $expiresAt : null,
            'payment_status' => $paymentStatus,
            'next_payment_due_at' => $data['next_payment_due_at'] ?? $expiresAt,
            'payment_grace_ends_at' => $data['payment_grace_ends_at'] ?? null,
            'grace_period_days' => $data['grace_period_days'] ?? null,
            'auto_renew' => (bool) ($data['auto_renew'] ?? false),
            'portal_locked' => $portalLocked,
            'deactivation_scheduled_at' => $data['deactivation_scheduled_at'] ?? null,
            'deactivation_reason' => $data['deactivation_reason'] ?? null,
            'contact_email' => $data['contact_email'] ?? $data['owner_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? $data['owner_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'school_type' => $data['school_type'] ?? null,
            'expected_students_count' => $data['expected_students_count'] ?? null,
            'subscription_status' => $subscriptionStatus,
        ];
    }

    private function brandingPayload(array $data, ?string $logoPath): array
    {
        return [
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color'],
            'accent_color' => $data['accent_color'],
            'logo_path' => $logoPath,
            'short_name' => $data['short_name'] ?? null,
            'portal_display_name' => $data['portal_display_name'] ?? $data['name'],
        ];
    }

    private function storeLogo(Request $request): ?string
    {
        return $request->hasFile('logo')
            ? $request->file('logo')->store('school-logos', 'public')
            : null;
    }
}
