<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    public function test_super_admin_can_view_any_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        
        $this->assertTrue($this->policy->viewAny($superAdmin));
    }

    public function test_non_super_admin_cannot_view_any_users(): void
    {
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $this->assertFalse($this->policy->viewAny($contentAdmin));
        $this->assertFalse($this->policy->viewAny($eventAdmin));
        $this->assertFalse($this->policy->viewAny($member));
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->view($user, $user));
    }

    public function test_super_admin_can_view_any_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $otherUser = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->view($superAdmin, $otherUser));
    }

    public function test_user_cannot_view_other_users(): void
    {
        $user1 = User::factory()->create(['role' => 'member']);
        $user2 = User::factory()->create(['role' => 'member']);
        
        $this->assertFalse($this->policy->view($user1, $user2));
    }

    public function test_only_super_admin_can_create_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->create($superAdmin));
        $this->assertFalse($this->policy->create($contentAdmin));
        $this->assertFalse($this->policy->create($eventAdmin));
        $this->assertFalse($this->policy->create($member));
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->update($user, $user));
    }

    public function test_super_admin_can_update_any_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $otherUser = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->update($superAdmin, $otherUser));
    }

    public function test_user_cannot_update_other_users(): void
    {
        $user1 = User::factory()->create(['role' => 'member']);
        $user2 = User::factory()->create(['role' => 'member']);
        
        $this->assertFalse($this->policy->update($user1, $user2));
    }

    public function test_super_admin_can_delete_other_users_but_not_themselves(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $otherUser = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->delete($superAdmin, $otherUser));
        $this->assertFalse($this->policy->delete($superAdmin, $superAdmin));
    }

    public function test_non_super_admin_cannot_delete_users(): void
    {
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        $otherUser = User::factory()->create(['role' => 'member']);
        
        $this->assertFalse($this->policy->delete($contentAdmin, $otherUser));
        $this->assertFalse($this->policy->delete($eventAdmin, $otherUser));
        $this->assertFalse($this->policy->delete($member, $otherUser));
    }

    public function test_only_super_admin_can_manage_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $this->assertTrue($this->policy->manageRoles($superAdmin));
        $this->assertFalse($this->policy->manageRoles($contentAdmin));
        $this->assertFalse($this->policy->manageRoles($eventAdmin));
        $this->assertFalse($this->policy->manageRoles($member));
    }

    public function test_super_admin_can_assign_roles_except_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        
        $this->assertTrue($this->policy->assignRole($superAdmin, 'content_admin'));
        $this->assertTrue($this->policy->assignRole($superAdmin, 'event_admin'));
        $this->assertTrue($this->policy->assignRole($superAdmin, 'member'));
        $this->assertFalse($this->policy->assignRole($superAdmin, 'super_admin'));
    }

    public function test_non_super_admin_cannot_assign_roles(): void
    {
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        
        $this->assertFalse($this->policy->assignRole($contentAdmin, 'member'));
        $this->assertFalse($this->policy->assignRole($contentAdmin, 'event_admin'));
    }
}
