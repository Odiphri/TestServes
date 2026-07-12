<?php

namespace App\Services\Notifications;

use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\User;
use App\Support\TenantDatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecipientResolver
{
    private const ALLOWED_NOTIFIABLE_TYPES = [
        SchoolOwner::class,
        User::class,
    ];

    public function __construct(private readonly TenantDatabaseManager $tenants)
    {
    }

    public function count(PlatformAdmin $admin, string $scope, array $payload = []): int
    {
        return $this->resolve($admin, $scope, $payload)->count();
    }

    public function resolve(PlatformAdmin $admin, string $scope, array $payload = []): Collection
    {
        $this->authorizeScope($admin, $scope, $payload);

        $targets = match ($scope) {
            'single_user' => $this->singleUser($payload),
            'multiple_users' => $this->multipleUsers($payload),
            'single_school' => $this->schoolOwners((array) ($payload['school_id'] ?? [])),
            'selected_schools' => $this->schoolOwners($payload['school_ids'] ?? []),
            'all_school_owners' => $this->allSchoolOwners(),
            'all_school_admins' => $this->tenantUsersByRole($this->eligibleSchoolIds(), ['admin']),
            'all_teachers' => $this->tenantUsersByRole($this->eligibleSchoolIds(), ['teacher']),
            'all_students' => $this->tenantUsersByRole($this->eligibleSchoolIds(), ['student', 'prefect']),
            'all_users_in_school' => $this->tenantUsersInSchools((array) ($payload['school_id'] ?? [])),
            'all_users_in_selected_schools' => $this->tenantUsersInSchools($payload['school_ids'] ?? []),
            'assigned_to_admin' => $this->assignedToAdmin($admin),
            'role' => $this->tenantUsersByRole($this->eligibleSchoolIds(), (array) ($payload['roles'] ?? $payload['role'] ?? [])),
            'all_users' => $this->allSchoolOwners(),
            'single_school_owner' => $this->singleSchoolOwner($payload),
            'selected_school_owners' => $this->selectedSchoolOwners($payload),
            'school_owners_for_school' => $this->schoolOwners((array) ($payload['school_id'] ?? [])),
            default => collect(),
        };

        return $targets
            ->filter()
            ->unique(fn (NotificationTarget $target) => $target->key())
            ->values();
    }

    private function authorizeScope(PlatformAdmin $admin, string $scope, array $payload): void
    {
        abort_unless($admin->canPerform('notifications.send'), 403);

        if (($payload['is_system_notification'] ?? false) && ! $admin->canPerform('notifications.system')) {
            abort(403);
        }

        if ($admin->isSuperAdmin()) {
            return;
        }

        if (in_array($scope, ['all_school_owners', 'all_users', 'all_school_admins', 'all_teachers', 'all_students'], true)) {
            abort_unless($admin->canPerform('notifications.platform_wide'), 403);
        }

        if ($admin->hasRole('sales_admin')) {
            abort_unless(in_array($scope, ['assigned_to_admin', 'single_school', 'school_owners_for_school', 'single_school_owner'], true), 403);
        }

        if ($admin->hasRole('finance_admin')) {
            abort_unless(in_array($scope, ['single_school_owner', 'selected_school_owners', 'school_owners_for_school'], true), 403);
        }

        if ($admin->hasRole('support_admin')) {
            abort_unless(in_array($scope, ['assigned_to_admin', 'single_school_owner', 'school_owners_for_school'], true), 403);
        }

        if ($admin->hasRole('operations_admin')) {
            abort_unless(in_array($scope, ['single_school_owner', 'selected_school_owners', 'school_owners_for_school', 'single_school'], true), 403);
        }
    }

    private function singleUser(array $payload): Collection
    {
        $type = $payload['notifiable_type'] ?? SchoolOwner::class;
        $id = (int) ($payload['notifiable_id'] ?? $payload['user_id'] ?? 0);
        $schoolId = $payload['school_id'] ?? null;

        if (! $id || ! in_array($type, self::ALLOWED_NOTIFIABLE_TYPES, true)) {
            return collect();
        }

        return collect([new NotificationTarget($type, $id, $schoolId ? (int) $schoolId : null)]);
    }

    private function multipleUsers(array $payload): Collection
    {
        return collect($payload['users'] ?? [])
            ->filter(fn ($user) => in_array($user['notifiable_type'] ?? SchoolOwner::class, self::ALLOWED_NOTIFIABLE_TYPES, true))
            ->map(fn ($user) => new NotificationTarget(
                $user['notifiable_type'] ?? SchoolOwner::class,
                (int) ($user['notifiable_id'] ?? $user['id']),
                isset($user['school_id']) ? (int) $user['school_id'] : null
            ));
    }

    private function singleSchoolOwner(array $payload): Collection
    {
        return SchoolOwner::query()
            ->whereKey($payload['school_owner_id'] ?? null)
            ->where('status', 'active')
            ->get()
            ->map(fn (SchoolOwner $owner) => new NotificationTarget(SchoolOwner::class, $owner->id, $owner->school_id));
    }

    private function selectedSchoolOwners(array $payload): Collection
    {
        return SchoolOwner::query()
            ->whereIn('id', array_unique($payload['school_owner_ids'] ?? []))
            ->where('status', 'active')
            ->get()
            ->map(fn (SchoolOwner $owner) => new NotificationTarget(SchoolOwner::class, $owner->id, $owner->school_id));
    }

    private function allSchoolOwners(): Collection
    {
        return SchoolOwner::query()
            ->where('status', 'active')
            ->get()
            ->map(fn (SchoolOwner $owner) => new NotificationTarget(SchoolOwner::class, $owner->id, $owner->school_id));
    }

    private function schoolOwners(array $schoolIds): Collection
    {
        return SchoolOwner::query()
            ->whereIn('school_id', array_filter(array_unique($schoolIds)))
            ->where('status', 'active')
            ->get()
            ->map(fn (SchoolOwner $owner) => new NotificationTarget(SchoolOwner::class, $owner->id, $owner->school_id));
    }

    private function assignedToAdmin(PlatformAdmin $admin): Collection
    {
        $schoolIds = collect()
            ->merge(DB::table('support_tickets')->where('assigned_admin_id', $admin->id)->pluck('school_id'))
            ->merge(DB::table('live_support_conversations')->where('assigned_admin_id', $admin->id)->pluck('school_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->schoolOwners($schoolIds);
    }

    private function tenantUsersByRole(array $schoolIds, array $roles): Collection
    {
        $roles = array_values(array_filter($roles));

        if ($roles === []) {
            throw ValidationException::withMessages(['role' => 'Choose at least one role.']);
        }

        return $this->tenantUsersInSchools($schoolIds, $roles);
    }

    private function tenantUsersInSchools(array $schoolIds, ?array $roles = null): Collection
    {
        $targets = collect();
        $schools = School::query()
            ->whereIn('id', array_filter(array_unique($schoolIds)))
            ->whereNotNull('tenant_database_created_at')
            ->get();

        foreach ($schools as $school) {
            if (! $this->tenants->databaseExists($school)) {
                continue;
            }

            $this->tenants->activateExisting($school);

            User::on('tenant')
                ->when($roles, fn ($query) => $query->whereIn('role', $roles))
                ->where('is_active', true)
                ->select('id')
                ->chunkById(500, function ($users) use ($targets, $school) {
                    foreach ($users as $user) {
                        $targets->push(new NotificationTarget(User::class, (int) $user->id, (int) $school->id));
                    }
                });

            DB::setDefaultConnection('mysql');
        }

        return $targets;
    }

    private function eligibleSchoolIds(): array
    {
        return School::query()
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();
    }
}
