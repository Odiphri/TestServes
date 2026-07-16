<?php

namespace Tests\Feature;

use App\Models\PaymentRecord;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SchoolPortalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_school_with_old_paid_payment_cannot_access_portal_pages(): void
    {
        $school = $this->school([
            'status' => 'deactivated',
            'subscription_status' => 'cancelled',
            'payment_status' => 'deactivated',
            'portal_locked' => true,
            'deactivation_reason' => 'Payment failed.',
            'deactivated_at' => now(),
            'subscription_expires_at' => now()->addMonth()->toDateString(),
        ]);

        PaymentRecord::create([
            'school_id' => $school->id,
            'amount' => 5000,
            'currency' => 'NGN',
            'payment_method' => 'manual',
            'payment_reference' => 'OLD-PAID',
            'status' => 'paid',
            'period_start' => now()->subMonth()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($this->tenantAdmin())
            ->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/admin/dashboard')
            ->assertStatus(402)
            ->assertSee('Portal locked', false)
            ->assertSee('This school portal has been deactivated.', false);
    }

    public function test_inactive_school_status_blocks_login_page_too(): void
    {
        foreach (['deactivated', 'suspended', 'expired', 'awaiting_payment'] as $status) {
            $school = $this->school([
                'status' => $status,
                'subscription_status' => in_array($status, ['expired'], true) ? 'expired' : 'cancelled',
                'payment_status' => $status === 'awaiting_payment' ? 'pending' : $status,
                'portal_locked' => true,
                'subscription_expires_at' => now()->subDay()->toDateString(),
                'subscription_ends_at' => now()->subDay(),
            ]);

            $this->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/login')
                ->assertStatus(402)
                ->assertSee('Portal locked', false);
        }
    }

    public function test_soft_deleted_school_subdomain_returns_not_found(): void
    {
        $school = $this->school([
            'status' => 'active',
            'subscription_status' => 'active',
            'payment_status' => 'paid',
            'portal_locked' => false,
            'subscription_expires_at' => now()->addMonth()->toDateString(),
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $school->delete();

        $this->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/login')
            ->assertStatus(404)
            ->assertSee('School Portal Not Found', false);
    }

    public function test_suspended_school_cannot_access_portal_pages(): void
    {
        $school = $this->school([
            'status' => 'suspended',
            'subscription_status' => 'cancelled',
            'payment_status' => 'suspended',
            'portal_locked' => true,
            'suspension_reason' => 'Unverified payment.',
            'subscription_expires_at' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($this->tenantAdmin())
            ->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/admin/dashboard')
            ->assertStatus(402)
            ->assertSee('Portal locked', false)
            ->assertSee('Unverified payment.', false);
    }

    public function test_admin_can_end_trial_and_lock_portal(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'trial-admin@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = $this->school([
            'status' => 'trial',
            'subscription_status' => 'trial',
            'payment_status' => 'trial',
            'portal_locked' => false,
            'subscription_expires_at' => now()->addDays(10)->toDateString(),
            'trial_ends_at' => now()->addDays(10),
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'expired']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('expired', $school->status);
        $this->assertSame('expired', $school->subscription_status);
        $this->assertSame('expired', $school->payment_status);
        $this->assertTrue($school->portal_locked);
        $this->assertNotNull($school->trial_ends_at);
        $this->assertNotNull($school->trial_ended_at);
        $this->assertNotNull($school->expired_at);
        $this->assertSame(now()->toDateString(), $school->subscription_expires_at->toDateString());
        $this->assertSame(now()->toDateString(), $school->next_payment_due_at->toDateString());

        $this->actingAs($this->tenantAdmin())
            ->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/admin/dashboard')
            ->assertStatus(402)
            ->assertSee('Portal locked', false);
    }

    public function test_admin_can_unexpire_school_and_restore_portal_access(): void
    {
        $admin = $this->platformAdmin();
        $school = $this->school([
            'status' => 'expired',
            'subscription_status' => 'expired',
            'payment_status' => 'expired',
            'portal_locked' => true,
            'subscription_expires_at' => now()->subDay()->toDateString(),
            'next_payment_due_at' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'active']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('active', $school->status);
        $this->assertSame('active', $school->subscription_status);
        $this->assertSame('paid', $school->payment_status);
        $this->assertFalse($school->portal_locked);
        $this->assertNotNull($school->activated_at);
        $this->assertNotNull($school->subscription_ends_at);
        $this->assertTrue($school->subscription_expires_at->endOfDay()->isFuture());
        $this->assertTrue($school->hasPortalAccess());
    }

    public function test_admin_can_unsuspend_school_and_restore_portal_access(): void
    {
        $admin = $this->platformAdmin('unsuspend-admin@example.com');
        $school = $this->school([
            'status' => 'suspended',
            'subscription_status' => 'cancelled',
            'payment_status' => 'suspended',
            'portal_locked' => true,
            'subscription_expires_at' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'active']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('active', $school->status);
        $this->assertSame('active', $school->subscription_status);
        $this->assertSame('paid', $school->payment_status);
        $this->assertFalse($school->portal_locked);
        $this->assertNotNull($school->activated_at);
        $this->assertTrue($school->hasPortalAccess());
    }

    public function test_admin_trial_button_starts_real_trial_countdown(): void
    {
        $admin = $this->platformAdmin('trial-start-admin@example.com');
        $school = $this->school([
            'status' => 'pending',
            'subscription_status' => 'pending',
            'payment_status' => 'pending',
            'portal_locked' => true,
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'trial']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('trial', $school->status);
        $this->assertSame('trial', $school->payment_status);
        $this->assertFalse($school->portal_locked);
        $this->assertNotNull($school->trial_ends_at);
        $this->assertSame($school->trial_ends_at->toDateString(), $school->next_payment_due_at->toDateString());
        $this->assertTrue($school->hasPortalAccess());
    }

    private function school(array $overrides = []): School
    {
        $slug = 'portal-lock-'.Str::lower(Str::random(8));

        return School::create($overrides + [
            'name' => 'Portal Lock School',
            'slug' => $slug,
            'portal_url' => 'https://'.$slug.'.'.config('testserves.root_domain'),
            'tenant_connection' => 'mysql',
            'tenant_database' => 'testserves_'.str_replace('-', '_', $slug),
            'tenant_database_created_at' => now(),
        ]);
    }

    private function tenantAdmin(): User
    {
        $token = Str::lower(Str::random(8));

        return User::create([
            'portal_id' => 'portal-admin-'.$token,
            'first_name' => 'Portal',
            'last_name' => 'Admin',
            'email' => 'portal-admin-'.$token.'@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function platformAdmin(string $email = 'trial-admin@example.com'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }
}
