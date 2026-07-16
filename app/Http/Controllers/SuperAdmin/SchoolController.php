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

        if (in_array($targetStatus, [SchoolLifecycle::SUSPENDED, SchoolLifecycle::DEACTIVATED, SchoolLifecycle::ARCHIVED], true)) {
            $settings = SystemSetting::values();
            $noticeDays = max(1, (int) ($settings['deactivated_school_delete_after_days'] ?? 30));
            $extra += [
                'deactivation_reason' => request('deactivation_reason') ?: 'The school was deactivated by TestServes administration.',
                'deactivated_at' => now(),
                'deactivation_scheduled_at' => now(),
                'delete_scheduled_at' => now()->addDays($noticeDays),
            ];
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
        $restored->update(['status' => SchoolLifecycle::AWAITING_PAYMENT]);
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
            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'trial', 'expired', 'deactivated'])],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_expires_at' => ['nullable', 'date', 'after_or_equal:subscription_starts_at'],
            'next_payment_due_at' => ['nullable', 'date'],
            'payment_grace_ends_at' => ['nullable', 'date'],
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

        return [
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'portal_url' => TestServesDomains::portalUrl($slug),
            'status' => $data['status'],
            'subscription_starts_at' => $data['subscription_starts_at'] ?? null,
            'subscription_expires_at' => $data['subscription_expires_at'] ?? null,
            'next_payment_due_at' => $data['next_payment_due_at'] ?? ($data['subscription_expires_at'] ?? null),
            'payment_grace_ends_at' => $data['payment_grace_ends_at'] ?? null,
            'deactivation_scheduled_at' => $data['deactivation_scheduled_at'] ?? null,
            'deactivation_reason' => $data['deactivation_reason'] ?? null,
            'contact_email' => $data['contact_email'] ?? $data['owner_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? $data['owner_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'school_type' => $data['school_type'] ?? null,
            'expected_students_count' => $data['expected_students_count'] ?? null,
            'subscription_status' => in_array($data['status'], ['active', 'trial', 'expired'], true) ? $data['status'] : (in_array($data['status'], ['suspended', 'deactivated'], true) ? 'cancelled' : 'pending'),
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
