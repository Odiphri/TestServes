<?php

namespace Tests\Feature;

use App\Models\NotificationCampaign;
use App\Models\NotificationRecipient;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_mark_notifications_read(): void
    {
        $school = School::create([
            'name' => 'Notice School',
            'slug' => 'notice-school',
            'status' => 'awaiting_payment',
            'subscription_status' => 'pending',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Notice Owner',
            'email' => 'notice-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $notification = $this->createRecipient($owner, $school, [
            'title' => 'Payment received',
            'body' => 'Your payment is being reviewed.',
        ]);

        $this->actingAs($owner, 'school_owner')
            ->get(route('platform.notifications.index'))
            ->assertOk()
            ->assertSee('Payment received')
            ->assertSee('Your payment is being reviewed.');

        $this->actingAs($owner, 'school_owner')
            ->post(route('platform.notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_tenant_user_notifications_are_scoped_by_school(): void
    {
        $school = School::create([
            'name' => 'Tenant Notice',
            'slug' => 'tenant-notice',
            'status' => 'active',
            'subscription_status' => 'active',
            'payment_status' => 'paid',
            'portal_locked' => false,
            'subscription_expires_at' => now()->addMonth(),
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $otherSchool = School::create([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant',
            'status' => 'active',
            'subscription_status' => 'active',
            'payment_status' => 'paid',
            'portal_locked' => false,
            'subscription_expires_at' => now()->addMonth(),
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $user = User::create([
            'portal_id' => 'tenant-admin',
            'first_name' => 'Tenant',
            'last_name' => 'Admin',
            'email' => 'tenant-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $ownNotification = $this->createRecipient($user, $school, [
            'title' => 'Your school notice',
            'body' => 'Visible only here.',
        ]);

        $otherNotification = $this->createRecipient($user, $otherSchool, [
            'title' => 'Other school notice',
            'body' => 'Must stay hidden.',
        ]);

        app()->instance('currentSchool', $school);
        view()->share('currentSchool', $school);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Your school notice')
            ->assertDontSee('Other school notice');

        $this->actingAs($user)
            ->post(route('notifications.read', $ownNotification))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('notifications.read', $otherNotification))
            ->assertForbidden();
    }

    public function test_platform_admin_can_send_notification_campaign_from_ui_route(): void
    {
        $admin = PlatformAdmin::create([
            'name' => 'Super Admin',
            'email' => 'notify-admin@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $school = School::create([
            'name' => 'Campaign School',
            'slug' => 'campaign-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $owner = SchoolOwner::create([
            'school_id' => $school->id,
            'name' => 'Campaign Owner',
            'email' => 'campaign-owner@example.com',
            'password' => 'password123',
            'is_primary' => true,
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'platform_admin')
            ->get(route('super-admin.notification-campaigns.create'))
            ->assertOk()
            ->assertSee('Send Notification');

        $this->actingAs($admin, 'platform_admin')
            ->post(route('super-admin.notification-campaigns.store'), [
                'title' => 'Portal reminder',
                'body' => 'Please complete your setup.',
                'type' => 'reminder',
                'recipient_scope' => 'single_school_owner',
                'school_owner_id' => $owner->id,
                'allows_replies' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notification_campaigns', [
            'title' => 'Portal reminder',
            'recipient_scope' => 'single_school_owner',
            'recipient_count' => 1,
        ]);

        $this->assertDatabaseHas('notification_recipients', [
            'notifiable_type' => SchoolOwner::class,
            'notifiable_id' => $owner->id,
            'school_id' => $school->id,
        ]);
    }

    private function createRecipient($recipient, School $school, array $attributes): NotificationRecipient
    {
        $campaign = NotificationCampaign::create([
            'school_id' => $school->id,
            'type' => 'general',
            'title' => $attributes['title'],
            'body' => $attributes['body'],
            'recipient_scope' => 'single_user',
            'is_system_notification' => false,
            'allows_replies' => true,
            'sent_at' => now(),
            'status' => 'sent',
            'recipient_count' => 1,
            'successful_deliveries' => 1,
        ]);

        return NotificationRecipient::create([
            'notification_campaign_id' => $campaign->id,
            'notifiable_type' => $recipient::class,
            'notifiable_id' => $recipient->getKey(),
            'school_id' => $school->id,
            'delivered_at' => now(),
        ]);
    }
}
