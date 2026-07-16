<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Support\PaymentApprovalService;
use App\Support\SubscriptionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_school_does_not_soft_delete_it(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'CYOLE Stars',
            'slug' => 'cyole',
            'portal_url' => 'https://cyole.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->patch(route('super-admin.schools.status', [$school, 'deactivated']))
            ->assertRedirect();

        $school->refresh();

        $this->assertSame('deactivated', $school->status);
        $this->assertSame('cancelled', $school->subscription_status);
        $this->assertNull($school->deleted_at);
        $this->assertNotNull($school->deactivated_at);
        $this->assertNotNull($school->delete_scheduled_at);
    }

    public function test_delete_school_soft_deletes_for_archived_restore(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'delete-super@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me',
            'portal_url' => 'https://delete-me.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->delete(route('super-admin.schools.destroy', $school))
            ->assertRedirect(route('super-admin.schools.index'));

        $this->assertSoftDeleted('schools', ['id' => $school->id]);
    }

    public function test_failed_payment_deactivates_school_without_deleting_it(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Finance Admin',
            'email' => 'finance@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Failed Payment School',
            'slug' => 'failed-payment-school',
            'portal_url' => 'https://failed-payment-school.testserves.com',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth()->toDateString(),
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'School Owner',
            'email' => 'owner-failed@example.com',
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
            'payment_reference' => 'FAIL-001',
            'status' => 'pending',
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
        ]);

        app(PaymentApprovalService::class)->mark($payment, 'failed', $admin, 'Receipt could not be verified.');

        $school->refresh();

        $this->assertSame('deactivated', $school->status);
        $this->assertSame('cancelled', $school->subscription_status);
        $this->assertNotNull($school->last_payment_failed_at);
        $this->assertNotNull($school->deactivated_at);
        $this->assertNull($school->deleted_at);
    }

    public function test_expired_subscription_enters_grace_then_deactivates_after_grace(): void
    {
        Carbon::setTestNow('2026-07-16 10:00:00');

        $school = School::create([
            'name' => 'Expired Grace School',
            'slug' => 'expired-grace-school',
            'portal_url' => 'https://expired-grace-school.testserves.com',
            'status' => 'trial',
            'subscription_status' => 'trial',
            'subscription_expires_at' => now()->subDay()->toDateString(),
        ]);

        SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Grace Owner',
            'email' => 'grace-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $refreshed = app(SubscriptionLifecycleService::class)->refresh($school);

        $this->assertSame('expired', $refreshed->status);
        $this->assertNotNull($refreshed->payment_grace_ends_at);
        $this->assertNotNull($refreshed->deactivation_scheduled_at);

        Carbon::setTestNow(now()->addDays(8));

        $deactivated = app(SubscriptionLifecycleService::class)->refresh($refreshed->fresh());

        $this->assertSame('deactivated', $deactivated->status);
        $this->assertSame('cancelled', $deactivated->subscription_status);
        $this->assertSame('deactivated', $deactivated->payment_status);
        $this->assertTrue($deactivated->portal_locked);
        $this->assertNotNull($deactivated->deactivated_at);

        Carbon::setTestNow();
    }
}
