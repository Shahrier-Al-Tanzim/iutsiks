<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Blog;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Registration;
use App\Models\PrayerTime;
use App\Models\GalleryImage;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = new User();
        $expected = ['name', 'email', 'password', 'role', 'phone', 'student_id'];
        
        $this->assertEquals($expected, $user->getFillable());
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = new User();
        $expected = ['password', 'remember_token'];
        
        $this->assertEquals($expected, $user->getHidden());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_has_authored_blogs_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->authoredBlogs());
    }

    /** @test */
    public function it_has_authored_events_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->authoredEvents());
    }

    /** @test */
    public function it_has_created_fests_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->createdFests());
    }

    /** @test */
    public function it_has_registrations_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->registrations());
    }

    /** @test */
    public function it_has_updated_prayer_times_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->updatedPrayerTimes());
    }

    /** @test */
    public function it_has_uploaded_images_relationship()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->uploadedImages());
    }

    /** @test */
    public function it_identifies_super_admin_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($member->isSuperAdmin());
    }

    /** @test */
    public function it_identifies_content_admin_correctly()
    {
        $contentAdmin = User::factory()->contentAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($contentAdmin->isContentAdmin());
        $this->assertFalse($member->isContentAdmin());
    }

    /** @test */
    public function it_identifies_event_admin_correctly()
    {
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($eventAdmin->isEventAdmin());
        $this->assertFalse($member->isEventAdmin());
    }

    /** @test */
    public function it_identifies_admin_users_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertTrue($contentAdmin->isAdmin());
        $this->assertTrue($eventAdmin->isAdmin());
        $this->assertFalse($member->isAdmin());
    }

    /** @test */
    public function it_checks_content_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageContent());
        $this->assertTrue($contentAdmin->canManageContent());
        $this->assertFalse($eventAdmin->canManageContent());
        $this->assertFalse($member->canManageContent());
    }

    /** @test */
    public function it_checks_event_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageEvents());
        $this->assertFalse($contentAdmin->canManageEvents());
        $this->assertTrue($eventAdmin->canManageEvents());
        $this->assertFalse($member->canManageEvents());
    }

    /** @test */
    public function it_checks_fest_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageFests());
        $this->assertFalse($contentAdmin->canManageFests());
        $this->assertTrue($eventAdmin->canManageFests());
        $this->assertFalse($member->canManageFests());
    }

    /** @test */
    public function it_checks_role_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        
        $this->assertTrue($superAdmin->hasRole('super_admin'));
        $this->assertTrue($superAdmin->hasRole(['super_admin', 'content_admin']));
        $this->assertFalse($superAdmin->hasRole('member'));
        
        $this->assertTrue($contentAdmin->hasRole('content_admin'));
        $this->assertFalse($contentAdmin->hasRole('super_admin'));
    }

    /** @test */
    public function it_checks_prayer_time_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManagePrayerTimes());
        $this->assertTrue($contentAdmin->canManagePrayerTimes());
        $this->assertFalse($eventAdmin->canManagePrayerTimes());
        $this->assertFalse($member->canManagePrayerTimes());
    }

    /** @test */
    public function it_checks_gallery_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageGallery());
        $this->assertTrue($contentAdmin->canManageGallery());
        $this->assertTrue($eventAdmin->canManageGallery());
        $this->assertFalse($member->canManageGallery());
    }

    /** @test */
    public function it_checks_registration_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageRegistrations());
        $this->assertFalse($contentAdmin->canManageRegistrations());
        $this->assertTrue($eventAdmin->canManageRegistrations());
        $this->assertFalse($member->canManageRegistrations());
    }

    /** @test */
    public function it_checks_user_management_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canManageUsers());
        $this->assertFalse($contentAdmin->canManageUsers());
        $this->assertFalse($eventAdmin->canManageUsers());
        $this->assertFalse($member->canManageUsers());
    }

    /** @test */
    public function it_checks_admin_dashboard_access_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canViewAdminDashboard());
        $this->assertTrue($contentAdmin->canViewAdminDashboard());
        $this->assertTrue($eventAdmin->canViewAdminDashboard());
        $this->assertFalse($member->canViewAdminDashboard());
    }

    /** @test */
    public function it_checks_registration_approval_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canApproveRegistrations());
        $this->assertFalse($contentAdmin->canApproveRegistrations());
        $this->assertTrue($eventAdmin->canApproveRegistrations());
        $this->assertFalse($member->canApproveRegistrations());
    }

    /** @test */
    public function it_checks_payment_verification_permissions_correctly()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->canVerifyPayments());
        $this->assertFalse($contentAdmin->canVerifyPayments());
        $this->assertTrue($eventAdmin->canVerifyPayments());
        $this->assertFalse($member->canVerifyPayments());
    }

    /** @test */
    public function it_checks_permissions_using_has_permission_method()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->member()->create();
        
        $this->assertTrue($superAdmin->hasPermission('manage_users'));
        $this->assertTrue($superAdmin->hasPermission('manage_events'));
        $this->assertTrue($superAdmin->hasPermission('manage_content'));
        $this->assertFalse($superAdmin->hasPermission('invalid_permission'));
        
        $this->assertFalse($member->hasPermission('manage_users'));
        $this->assertFalse($member->hasPermission('manage_events'));
    }
}