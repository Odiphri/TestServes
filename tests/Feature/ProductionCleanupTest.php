<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\DemoRequest;
use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_delete_school_owner_account(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Cleanup School',
            'slug' => 'cleanup-school',
            'portal_url' => 'https://cleanup-school.testserves.com',
            'status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Cleanup Owner',
            'email' => 'cleanup-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.school-owners.destroy', $owner))
            ->assertRedirect(route('super-admin.school-owners.index'));

        $this->assertDatabaseMissing('school_owners', ['id' => $owner->id]);
        $this->assertDatabaseHas('schools', ['id' => $school->id]);
    }

    public function test_super_admin_can_search_platform_admin_users(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-search-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        PlatformAdmin::create([
            'name' => 'Finance Search Target',
            'email' => 'finance-target@example.com',
            'password' => 'password123',
            'role' => 'finance_admin',
            'is_active' => true,
        ]);

        PlatformAdmin::create([
            'name' => 'Support Hidden',
            'email' => 'support-hidden@example.com',
            'password' => 'password123',
            'role' => 'support_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->get(route('super-admin.admin-users.index', ['search' => 'finance-target']))
            ->assertOk()
            ->assertSee('Finance Search Target')
            ->assertDontSee('Support Hidden');
    }

    public function test_super_admin_can_delete_platform_admin_user(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-delete-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $target = PlatformAdmin::create([
            'name' => 'Delete Platform User',
            'email' => 'delete-platform-user@example.com',
            'password' => 'password123',
            'role' => 'support_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.admin-users.destroy', $target))
            ->assertRedirect();

        $this->assertSoftDeleted('platform_admins', ['id' => $target->id]);
    }

    public function test_school_admin_can_delete_tenant_user(): void
    {
        $admin = User::create([
            'portal_id' => 'cleanup-admin',
            'first_name' => 'Cleanup',
            'last_name' => 'Admin',
            'email' => 'cleanup-admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $student = User::create([
            'portal_id' => 'cleanup-student',
            'first_name' => 'Cleanup',
            'last_name' => 'Student',
            'email' => 'cleanup-student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_super_admin_can_delete_payment_record(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'cleanup-payment-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $payment = PaymentRecord::create([
            'amount' => 5000,
            'currency' => 'NGN',
            'payment_method' => 'manual',
            'payment_reference' => 'PAY-DELETE',
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.payments.destroy', $payment))
            ->assertRedirect(route('super-admin.payments.index'));

        $this->assertSoftDeleted('payment_records', ['id' => $payment->id]);
    }

    public function test_owner_can_delete_unpaid_payment_submission(): void
    {
        $school = School::create([
            'name' => 'Owner Payment School',
            'slug' => 'owner-payment-school',
            'portal_url' => 'https://owner-payment-school.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Owner Payment',
            'email' => 'owner-payment-delete@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $payment = PaymentRecord::create([
            'school_id' => $school->id,
            'school_owner_id' => $owner->id,
            'amount' => 5000,
            'currency' => 'NGN',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'OWNER-PAY-DELETE',
            'status' => 'pending',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->delete(route('platform.payments.destroy', $payment))
            ->assertRedirect();

        $this->assertSoftDeleted('payment_records', ['id' => $payment->id]);
    }

    public function test_owner_demo_page_and_request_work(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Demo Plan',
            'slug' => 'demo-plan',
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'trial_days' => 7,
            'admin_limit' => 2,
            'features' => ['Admin dashboard', 'Exam creation'],
            'status' => 'active',
        ]);

        $school = School::create([
            'name' => 'Demo School',
            'slug' => 'demo-school',
            'portal_url' => 'https://demo-school.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Demo Owner',
            'email' => 'demo-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->get(route('platform.demo'))
            ->assertOk()
            ->assertSee('Request plan demo')
            ->assertSee('Exam creation');

        $this->actingAs($owner, 'school_owner')
            ->post(route('platform.demo.store'), [
                'subscription_plan_id' => $plan->id,
                'message' => 'Need demo access',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('demo_requests', [
            'school_owner_id' => $owner->id,
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'message' => 'Need demo access',
        ]);
    }

    public function test_owner_can_delete_own_demo_request(): void
    {
        $school = School::create([
            'name' => 'Demo Delete School',
            'slug' => 'demo-delete-school',
            'portal_url' => 'https://demo-delete-school.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Demo Delete Owner',
            'email' => 'demo-delete-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $demoRequest = DemoRequest::create([
            'school_owner_id' => $owner->id,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'contact_person' => $owner->name,
            'email' => $owner->email,
            'status' => 'new',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->delete(route('platform.demo.destroy', $demoRequest))
            ->assertRedirect();

        $this->assertSoftDeleted('demo_requests', ['id' => $demoRequest->id]);
    }

    public function test_super_admin_approval_generates_demo_link(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'demo-approval-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Demo Approval Plan',
            'slug' => 'demo-approval-plan',
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'trial_days' => 7,
            'admin_limit' => 1,
            'features' => ['Admin dashboard'],
            'status' => 'active',
        ]);

        $demoRequest = DemoRequest::create([
            'subscription_plan_id' => $plan->id,
            'school_name' => 'Approval School',
            'contact_person' => 'Approval Owner',
            'email' => 'approval-owner@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->put(route('super-admin.demo-requests.update', $demoRequest), [
                'school_name' => 'Approval School',
                'contact_person' => 'Approval Owner',
                'email' => 'approval-owner@example.com',
                'subscription_plan_id' => $plan->id,
                'status' => 'approved',
            ])
            ->assertRedirect(route('super-admin.demo-requests.show', $demoRequest));

        $demoRequest->refresh();

        $this->assertSame('approved', $demoRequest->status);
        $this->assertNotNull($demoRequest->demo_token);
        $this->assertNotNull($demoRequest->demo_access_token);
        $this->assertNotNull($demoRequest->demo_url);
    }

    public function test_owner_portal_admin_page_shows_plan_limit_before_tenant_exists(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Admin Limit Plan',
            'slug' => 'admin-limit-plan',
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'trial_days' => 7,
            'admin_limit' => 3,
            'features' => ['Admin dashboard'],
            'status' => 'active',
        ]);

        $school = School::create([
            'subscription_plan_id' => $plan->id,
            'name' => 'Admin Limit School',
            'slug' => 'admin-limit-school',
            'portal_url' => 'https://admin-limit-school.testserves.com',
            'status' => 'pending',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Limit Owner',
            'email' => 'limit-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->get(route('platform.portal-admins'))
            ->assertOk()
            ->assertSee('allows 3 CBT admin accounts')
            ->assertSee('Start a free trial or complete payment approval');
    }

    public function test_cbt_user_management_no_longer_creates_admin_users(): void
    {
        $admin = User::create([
            'portal_id' => 'cleanup-admin-no-create',
            'first_name' => 'Cleanup',
            'last_name' => 'Admin',
            'email' => 'cleanup-admin-no-create@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertDontSee('Create Admin User');
    }
}
