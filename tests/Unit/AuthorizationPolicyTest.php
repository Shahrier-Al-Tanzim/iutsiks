<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Blog;
use App\Models\Registration;
use App\Models\PrayerTime;
use App\Models\GalleryImage;
use App\Policies\EventPolicy;
use App\Policies\FestPolicy;
use App\Policies\BlogPolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\PrayerTimePolicy;
use App\Policies\GalleryImagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_policy_authorization(): void
    {
        $policy = new EventPolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $event = Event::factory()->create(['author_id' => $eventAdmin->id, 'status' => 'published']);
        
        // Test viewAny - anyone can view
        $this->assertTrue($policy->viewAny(null));
        $this->assertTrue($policy->viewAny($member));
        
        // Test view published events - anyone can view
        $this->assertTrue($policy->view(null, $event));
        $this->assertTrue($policy->view($member, $event));
        
        // Test create - only event managers
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->create($eventAdmin));
        $this->assertFalse($policy->create($contentAdmin));
        $this->assertFalse($policy->create($member));
        
        // Test update - super admin or author
        $this->assertTrue($policy->update($superAdmin, $event));
        $this->assertTrue($policy->update($eventAdmin, $event));
        $this->assertFalse($policy->update($contentAdmin, $event));
        $this->assertFalse($policy->update($member, $event));
        
        // Test delete - super admin or author (if no registrations)
        $this->assertTrue($policy->delete($superAdmin, $event));
        $this->assertTrue($policy->delete($eventAdmin, $event));
        $this->assertFalse($policy->delete($contentAdmin, $event));
        $this->assertFalse($policy->delete($member, $event));
    }

    public function test_fest_policy_authorization(): void
    {
        $policy = new FestPolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $fest = Fest::factory()->create(['created_by' => $eventAdmin->id, 'status' => 'published']);
        
        // Test viewAny - anyone can view
        $this->assertTrue($policy->viewAny(null));
        
        // Test view published fests - anyone can view
        $this->assertTrue($policy->view(null, $fest));
        
        // Test create - only event managers
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->create($eventAdmin));
        $this->assertFalse($policy->create($contentAdmin));
        $this->assertFalse($policy->create($member));
        
        // Test update - super admin or creator
        $this->assertTrue($policy->update($superAdmin, $fest));
        $this->assertTrue($policy->update($eventAdmin, $fest));
        $this->assertFalse($policy->update($contentAdmin, $fest));
        $this->assertFalse($policy->update($member, $fest));
    }

    public function test_blog_policy_authorization(): void
    {
        $policy = new BlogPolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $blog = Blog::factory()->create(['author_id' => $contentAdmin->id]);
        
        // Test viewAny - anyone can view
        $this->assertTrue($policy->viewAny(null));
        
        // Test view - anyone can view
        $this->assertTrue($policy->view(null, $blog));
        
        // Test create - only content managers
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->create($contentAdmin));
        $this->assertFalse($policy->create($eventAdmin));
        $this->assertFalse($policy->create($member));
        
        // Test update - super admin or author
        $this->assertTrue($policy->update($superAdmin, $blog));
        $this->assertTrue($policy->update($contentAdmin, $blog));
        $this->assertFalse($policy->update($eventAdmin, $blog));
        $this->assertFalse($policy->update($member, $blog));
    }

    public function test_registration_policy_authorization(): void
    {
        $policy = new RegistrationPolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $event = Event::factory()->create(['author_id' => $eventAdmin->id]);
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'pending'
        ]);
        
        // Test viewAny - only event managers
        $this->assertTrue($policy->viewAny($superAdmin));
        $this->assertTrue($policy->viewAny($eventAdmin));
        $this->assertFalse($policy->viewAny($contentAdmin));
        $this->assertFalse($policy->viewAny($member));
        
        // Test view - user's own registration, admins, or event author
        $this->assertTrue($policy->view($member, $registration));
        $this->assertTrue($policy->view($superAdmin, $registration));
        $this->assertTrue($policy->view($eventAdmin, $registration));
        $this->assertFalse($policy->view($contentAdmin, $registration));
        
        // Test create - any authenticated user
        $this->assertTrue($policy->create($member));
        $this->assertTrue($policy->create($eventAdmin));
        
        // Test approve - event managers or event author
        $this->assertTrue($policy->approve($superAdmin, $registration));
        $this->assertTrue($policy->approve($eventAdmin, $registration));
        $this->assertFalse($policy->approve($contentAdmin, $registration));
        $this->assertFalse($policy->approve($member, $registration));
    }

    public function test_prayer_time_policy_authorization(): void
    {
        $policy = new PrayerTimePolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $prayerTime = PrayerTime::factory()->create(['updated_by' => $contentAdmin->id]);
        
        // Test viewAny - anyone can view
        $this->assertTrue($policy->viewAny(null));
        $this->assertTrue($policy->viewAny($member));
        
        // Test view - anyone can view
        $this->assertTrue($policy->view(null, $prayerTime));
        
        // Test create - only content managers
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->create($contentAdmin));
        $this->assertFalse($policy->create($eventAdmin));
        $this->assertFalse($policy->create($member));
        
        // Test update - only content managers
        $this->assertTrue($policy->update($superAdmin, $prayerTime));
        $this->assertTrue($policy->update($contentAdmin, $prayerTime));
        $this->assertFalse($policy->update($eventAdmin, $prayerTime));
        $this->assertFalse($policy->update($member, $prayerTime));
    }

    public function test_gallery_image_policy_authorization(): void
    {
        $policy = new GalleryImagePolicy();
        
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $member = User::factory()->create(['role' => 'member']);
        
        $galleryImage = GalleryImage::factory()->create(['uploaded_by' => $contentAdmin->id]);
        
        // Test viewAny - anyone can view
        $this->assertTrue($policy->viewAny(null));
        $this->assertTrue($policy->viewAny($member));
        
        // Test view - anyone can view
        $this->assertTrue($policy->view(null, $galleryImage));
        
        // Test create - any admin
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->create($contentAdmin));
        $this->assertTrue($policy->create($eventAdmin));
        $this->assertFalse($policy->create($member));
        
        // Test update - super admin or uploader
        $this->assertTrue($policy->update($superAdmin, $galleryImage));
        $this->assertTrue($policy->update($contentAdmin, $galleryImage));
        $this->assertFalse($policy->update($eventAdmin, $galleryImage));
        $this->assertFalse($policy->update($member, $galleryImage));
        
        // Test delete - super admin or uploader
        $this->assertTrue($policy->delete($superAdmin, $galleryImage));
        $this->assertTrue($policy->delete($contentAdmin, $galleryImage));
        $this->assertFalse($policy->delete($eventAdmin, $galleryImage));
        $this->assertFalse($policy->delete($member, $galleryImage));
    }
}
