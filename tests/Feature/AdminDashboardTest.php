<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Blog;
use App\Models\Registration;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_dashboard()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_regular_user_cannot_access_dashboard()
    {
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_dashboard_displays_system_statistics()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        
        // Create some test data
        User::factory()->count(5)->create();
        Event::factory()->count(3)->create();
        Fest::factory()->count(2)->create();
        Blog::factory()->count(4)->create();

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $response->assertViewHas('recentStats');
        $response->assertViewHas('registrationStats');
    }

    public function test_user_management_page_accessible_by_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.user-management');
    }

    public function test_analytics_page_accessible_by_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics');
    }

    public function test_system_settings_page_accessible_by_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system-settings');
    }

    public function test_activity_logs_page_accessible_by_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get('/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertViewIs('admin.activity-logs');
    }

    public function test_super_admin_can_update_user_role()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($superAdmin)->patch("/admin/users/{$user->id}/role", [
            'role' => 'event_admin',
            'reason' => 'Promoting to event admin'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'event_admin'
        ]);
    }

    public function test_super_admin_can_reset_user_password()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($superAdmin)->patch("/admin/users/{$user->id}/password", [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
            'reason' => 'Password reset requested'
        ]);

        $response->assertRedirect();
        
        // Verify password was changed
        $user->refresh();
        $this->assertTrue(\Hash::check('newpassword123', $user->password));
    }

    public function test_admin_activity_is_logged()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'member']);

        $this->actingAs($superAdmin)->patch("/admin/users/{$user->id}/role", [
            'role' => 'event_admin',
            'reason' => 'Promoting to event admin'
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_user_id' => $superAdmin->id,
            'action' => 'user_role_changed'
        ]);
    }
}