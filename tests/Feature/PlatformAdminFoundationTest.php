<?php

namespace Tests\Feature;

use App\Models\NotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\PaymentRecord;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SchoolLifecycleHistory;
use App\Models\SchoolOwner;
use App\Models\SubscriptionPlan;
use App\Support\NotificationCampaignService;
use App\Support\PaymentApprovalService;
use App\Support\PlatformAdminAccess;
use App\Support\SchoolLifecycle;
use App\Support\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlatformAdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_admin_role_exists_and_has_limited_sections(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Ops Admin',
            'email' => 'ops@example.com',
            'password' => 'password123',
            'role' => 'operations_admin',
            'is_active' => true,
        ]);

        $this->assertContains('operations_admin', PlatformAdminAccess::roles());
        $this->assertTrue($admin->canAccessPlatformSection('schools'));
        $this->assertFalse($admin->canAccessPlatformSection('payments'));
        $this->assertFalse($admin->canPerform('payments.approve'));
        $this->assertTrue($admin->canPerform('operations.manage'));
    }

    public function test_school_lifecycle_transition_records_history(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'life@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Lifecycle School',
            'slug' => 'lifecycle-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        app(SchoolLifecycle::class)->transition($school, 'suspended', $admin, 'Testing suspension');

        $school->refresh();

        $this->assertSame('suspended', $school->status);
        $this->assertSame('cancelled', $school->subscription_status);
        $this->assertDatabaseHas('school_lifecycle_histories', [
            'school_id' => $school->id,
            'previous_status' => 'active',
            'new_status' => 'suspended',
            'changed_by_admin_id' => $admin->id,
            'reason' => 'Testing suspension',
        ]);
    }

    public function test_invalid_lifecycle_transition_is_rejected_for_non_super_admin(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Ops Admin',
            'email' => 'ops-life@example.com',
            'password' => 'password123',
            'role' => 'operations_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Invalid Lifecycle',
            'slug' => 'invalid-life',
            'status' => 'new',
            'subscription_status' => 'pending',
        ]);

        $this->expectException(ValidationException::class);

        app(SchoolLifecycle::class)->transition($school, 'active', $admin, 'Skip steps');
    }

    public function test_duplicate_paid_payment_reference_cannot_activate_twice(): void
    {
        $this->mock(TenantDatabaseManager::class)
            ->shouldReceive('createAndMigrate')
            ->once();

        $admin = PlatformAdmin::create([
            'name' => 'Finance Admin',
            'email' => 'finance@example.com',
            'password' => 'password123',
            'role' => 'finance_admin',
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter-foundation',
            'monthly_price' => 1000,
            'yearly_price' => 10000,
            'status' => 'active',
        ]);

        $school = School::create([
            'subscription_plan_id' => $plan->id,
            'name' => 'Payment School',
            'slug' => 'payment-school',
            'status' => 'awaiting_payment',
            'subscription_status' => 'pending',
        ]);

        $first = PaymentRecord::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'amount' => 1000,
            'currency' => 'NGN',
            'payment_method' => 'manual',
            'payment_reference' => 'DUPLICATE-REF',
            'status' => 'pending',
        ]);

        $second = PaymentRecord::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'amount' => 1000,
            'currency' => 'NGN',
            'payment_method' => 'manual',
            'payment_reference' => 'DUPLICATE-REF',
            'status' => 'pending',
        ]);

        app(PaymentApprovalService::class)->mark($first, 'paid', $admin);

        $this->expectException(ValidationException::class);

        app(PaymentApprovalService::class)->mark($second, 'paid', $admin);
    }

    public function test_welcome_notification_for_owner_is_idempotent(): void
    {
        $school = School::create([
            'name' => 'Welcome School',
            'slug' => 'welcome-school',
            'status' => 'awaiting_payment',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Welcome Owner',
            'email' => 'welcome@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $service = app(NotificationCampaignService::class);
        $service->sendWelcome($owner, 'Welcome to TestServes', 'Your account has been created.', $school);
        $service->sendWelcome($owner, 'Welcome to TestServes', 'Your account has been created.', $school);

        $this->assertSame(1, NotificationCampaign::where('type', 'welcome')->count());
        $this->assertSame(1, NotificationRecipient::count());
    }
}
