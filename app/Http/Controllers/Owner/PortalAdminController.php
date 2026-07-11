<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Support\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PortalAdminController extends Controller
{
    public function index(TenantDatabaseManager $tenants)
    {
        [$owner, $school, $tenantReady, $admins, $adminLimit] = $this->pageState($tenants);

        return view('owner.portal-admins', compact('owner', 'school', 'tenantReady', 'admins', 'adminLimit'));
    }

    public function store(Request $request, TenantDatabaseManager $tenants)
    {
        [$owner, $school, $tenantReady, $admins, $adminLimit] = $this->pageState($tenants);

        abort_unless($school, 404);

        if (! $tenantReady) {
            return back()->with('error', 'Start a trial or activate payment before creating CBT portal admin accounts.');
        }

        if ($admins->count() >= $adminLimit) {
            throw ValidationException::withMessages([
                'portal_id' => "This plan allows {$adminLimit} CBT admin account(s). Upgrade the plan or remove an admin first.",
            ]);
        }

        $data = $request->validate([
            'portal_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $exists = DB::connection('tenant')->table('users')
            ->where('portal_id', $data['portal_id'])
            ->when(filled($data['email'] ?? null), fn ($query) => $query->orWhere('email', $data['email']))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'portal_id' => 'That portal ID or email is already used in this school portal.',
            ]);
        }

        [$firstName, $lastName] = $this->splitName($data['name']);

        DB::connection('tenant')->table('users')->insert([
            'portal_id' => $data['portal_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'CBT portal admin created.');
    }

    public function destroy(Request $request, TenantDatabaseManager $tenants, int $admin)
    {
        [$owner, $school, $tenantReady] = $this->pageState($tenants);

        abort_unless($school && $tenantReady, 404);

        $target = DB::connection('tenant')->table('users')
            ->where('id', $admin)
            ->where('role', 'admin')
            ->first();

        abort_unless($target, 404);

        if ($target->email === $owner->email || $target->portal_id === $owner->email) {
            return back()->with('error', 'The primary owner admin account cannot be deleted here.');
        }

        DB::connection('tenant')->table('users')->where('id', $target->id)->delete();

        return back()->with('success', 'CBT portal admin deleted.');
    }

    private function pageState(TenantDatabaseManager $tenants): array
    {
        $owner = Auth::guard('school_owner')->user();
        $owner->load(['school.plan']);
        $school = $owner->school;
        $tenantReady = false;
        $admins = collect();
        $adminLimit = max(1, (int) ($school?->plan?->admin_limit ?? 1));

        if ($school && $school->tenant_database_created_at) {
            try {
                if ($tenants->databaseExists($school)) {
                    $tenants->syncExistingTenant($school);
                    $tenantReady = true;
                    $admins = DB::connection('tenant')->table('users')
                        ->where('role', 'admin')
                        ->orderBy('id')
                        ->get();
                }
            } catch (\Throwable) {
                $tenantReady = false;
            }
        }

        return [$owner, $school, $tenantReady, $admins, $adminLimit];
    }

    private function splitName(string $name): array
    {
        $parts = collect(explode(' ', trim($name)))->filter()->values();
        $firstName = $parts->first() ?: 'Portal';
        $lastName = $parts->slice(1)->implode(' ') ?: 'Admin';

        return [$firstName, $lastName];
    }
}
