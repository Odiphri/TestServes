<?php

namespace Tests\Feature;

use App\Models\PaymentRecord;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolPortalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_school_with_old_paid_payment_cannot_access_portal_pages(): void
    {
        $school = $this->school([
            'status' => 'deactivated',
            'subscription_status' => 'cancelled',
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
            ->assertSee('Portal locked', false);
    }

    public function test_soft_deleted_school_subdomain_returns_not_found(): void
    {
        $school = $this->school([
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth()->toDateString(),
        ]);

        $school->delete();

        $this->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/login')
            ->assertStatus(404)
            ->assertSee('School Portal Not Found', false);
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
            'subscription_expires_at' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'expired']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('expired', $school->status);
        $this->assertSame('expired', $school->subscription_status);
        $this->assertSame(now()->toDateString(), $school->subscription_expires_at->toDateString());
        $this->assertSame(now()->toDateString(), $school->next_payment_due_at->toDateString());

        $this->actingAs($this->tenantAdmin())
            ->get('https://'.$school->slug.'.'.config('testserves.root_domain').'/admin/dashboard')
            ->assertStatus(402)
            ->assertSee('Portal locked', false);
    }

    private function school(array $overrides = []): School
    {
        return School::create($overrides + [
            'name' => 'Portal Lock School',
            'slug' => 'portal-lock-school',
            'portal_url' => 'https://portal-lock-school.'.config('testserves.root_domain'),
            'tenant_connection' => 'mysql',
            'tenant_database' => 'testserves_portal_lock_school',
            'tenant_database_created_at' => now(),
        ]);
    }

    private function tenantAdmin(): User
    {
        return User::create([
            'portal_id' => 'portal-admin',
            'first_name' => 'Portal',
            'last_name' => 'Admin',
            'email' => 'portal-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }
}
