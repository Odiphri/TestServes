<?php

namespace Tests\Feature;

use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrafficAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_traffic_log_and_logout_closes_it(): void
    {
        $user = $this->user('traffic-teacher-login', 'teacher');

        $this->post(route('login.submit'), [
            'portal_id' => $user->portal_id,
            'password' => 'password',
        ])->assertRedirect(route('teacher.dashboard'));

        $log = TrafficLog::where('user_id', $user->id)->first();

        $this->assertNotNull($log);
        $this->assertNull($log->logout_at);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertNotNull($log->fresh()->logout_at);
    }

    public function test_admin_activity_is_never_logged_or_returned_in_analytics(): void
    {
        $admin = $this->user('traffic-admin-hidden', 'admin');
        $teacher = $this->user('traffic-visible-teacher', 'teacher');
        $cbt = $this->user('traffic-cbt-viewer', 'cbt_personnel');

        $this->post(route('login.submit'), [
            'portal_id' => $admin->portal_id,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('traffic_logs', [
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);

        TrafficLog::create([
            'session_id' => 'legacy-admin-session',
            'user_id' => $admin->id,
            'user_name' => $admin->full_name,
            'role' => 'admin',
            'login_at' => now(),
            'last_activity_at' => now(),
        ]);

        TrafficLog::create([
            'session_id' => 'teacher-session',
            'user_id' => $teacher->id,
            'user_name' => $teacher->full_name,
            'role' => 'teacher',
            'login_at' => now(),
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($cbt)
            ->getJson(route('traffic.data', ['range' => 'daily']))
            ->assertOk()
            ->json();

        $this->assertSame(1, $response['total_visitors']);
        $this->assertArrayNotHasKey('admin', $response['role_breakdown']);
        $this->assertNotContains('admin', $response['roles']);
        $this->assertSame($teacher->full_name, $response['recent_visitors'][0]['name']);
    }

    public function test_traffic_page_is_limited_to_admin_hod_and_cbt_roles(): void
    {
        $teacher = $this->user('traffic-teacher', 'teacher');
        $cbt = $this->user('traffic-cbt', 'cbt_personnel');

        $this->actingAs($teacher)->get(route('traffic.index'))->assertForbidden();
        $this->actingAs($teacher)->getJson(route('traffic.data'))->assertForbidden();

        $this->actingAs($cbt)->get(route('traffic.index'))->assertOk();
        $this->actingAs($cbt)->getJson(route('traffic.data'))->assertOk();
    }

    private function user(string $portalId, string $role): User
    {
        return User::create([
            'portal_id' => $portalId,
            'first_name' => ucfirst(str_replace('_', ' ', $role)),
            'last_name' => 'User',
            'email' => "{$portalId}@example.com",
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }
}
