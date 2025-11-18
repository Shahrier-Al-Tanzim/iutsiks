<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use App\Models\Registration;
use App\Models\PrayerTime;
use App\Models\GalleryImage;
use App\Models\Blog;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminWorkflowIntegrationTest extends TestCase
{
    /** @test */
    public function super_admin_can_manage_complete_fest_workflow()
    {
        Storage::fake('public');
        $superAdmin = $this->createSuperAdmin();

        // Step 1: Create a fest
        $response = $this->actingAs($superAdmin)
            ->post(route('fests.store'), [
                'title' => 'Annual Tech Fest 2024',
                'description' => 'A comprehensive technology festival featuring various competitions and workshops.',
                'start_date' => now()->addMonth()->toDateString(),
                'end_date' => now()->addMonth()->addDays(3)->toDateString(),
                'banner_image' => UploadedFile::fake()->image('fest_banner.jpg')
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fest = Fest::where('title', 'Annual Tech Fest 2024')->first();
        $this->assertNotNull($fest);
        $this->assertEquals($superAdmin->id, $fest->created_by);

        // Step 2: Add events to the fest
        $events = [
            [
                'title' => 'Programming Contest',
                'type' => 'competition',
                'registration_type' => 'team',
                'fee_amount' => 200,
                'max_participants' => 50
            ],
            [
                'title' => 'Tech Talk on AI',
                'type' => 'lecture',
                'registration_type' => 'individual',
                'fee_amount' => 0,
                'max_participants' => 100
            ]
        ];

        foreach ($events as $eventData) {
            $this->actingAs($superAdmin)
                ->post(route('events.store'), array_merge($eventData, [
                    'fest_id' => $fest->id,
                    'description' => 'Event description',
                    'event_date' => now()->addMonth()->addDay()->toDateString(),
                    'event_time' => '10:00',
                    'location' => 'Main Auditorium',
                    'registration_deadline' => now()->addMonth()->toDateString(),
                    'status' => 'published'
                ]))
                ->assertRedirect()
                ->assertSessionHas('success');
        }

        $this->assertEquals(2, $fest->events()->count());

        // Step 3: Manage registrations for events
        $event = $fest->events()->first();
        $users = User::factory()->count(3)->create();

        // Create some registrations
        foreach ($users as $user) {
            Registration::factory()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_required' => $event->fee_amount > 0,
                'payment_status' => $event->fee_amount > 0 ? 'pending' : null
            ]);
        }

        // Admin views registrations
        $this->actingAs($superAdmin)
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertSee($event->title);

        // Admin approves registrations
        $registrations = Registration::where('event_id', $event->id)->get();
        foreach ($registrations as $registration) {
            if ($registration->payment_required) {
                $this->actingAs($superAdmin)
                    ->patch(route('admin.registrations.verify-payment', $registration), [
                        'action' => 'approve',
                        'admin_notes' => 'Payment verified'
                    ]);
            }

            $this->actingAs($superAdmin)
                ->patch(route('admin.registrations.approve', $registration))
                ->assertRedirect();
        }

        // Verify all registrations are approved
        $this->assertEquals(3, Registration::where('event_id', $event->id)
            ->where('status', 'approved')->count());

        // Step 4: Add gallery images to fest
        $this->actingAs($superAdmin)
            ->post(route('gallery.upload'), [
                'images' => [
                    UploadedFile::fake()->image('fest_photo1.jpg'),
                    UploadedFile::fake()->image('fest_photo2.jpg')
                ],
                'imageable_type' => 'fest',
                'imageable_id' => $fest->id,
                'captions' => ['Opening ceremony', 'Award distribution']
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(2, $fest->gallery()->count());
    }

    /** @test */
    public function event_admin_can_manage_event_lifecycle()
    {
        $eventAdmin = $this->createEventAdmin();
        $fest = Fest::factory()->create();

        // Create event
        $response = $this->actingAs($eventAdmin)
            ->post(route('events.store'), [
                'fest_id' => $fest->id,
                'title' => 'Quiz Competition',
                'description' => 'General knowledge quiz competition',
                'event_date' => now()->addWeek()->toDateString(),
                'event_time' => '14:00',
                'type' => 'quiz',
                'registration_type' => 'individual',
                'location' => 'Room 101',
                'max_participants' => 30,
                'fee_amount' => 50,
                'registration_deadline' => now()->addDays(5)->toDateString(),
                'status' => 'published'
            ]);

        $response->assertRedirect();
        $event = Event::where('title', 'Quiz Competition')->first();
        $this->assertNotNull($event);

        // Create registrations
        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            Registration::factory()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'registration_type' => 'individual',
                'payment_required' => true,
                'payment_amount' => 50,
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);
        }

        // Admin manages registrations
        $registrations = Registration::where('event_id', $event->id)->get();
        
        // Approve some payments, reject others
        $this->actingAs($eventAdmin)
            ->patch(route('admin.registrations.verify-payment', $registrations[0]), [
                'action' => 'approve',
                'admin_notes' => 'Payment verified'
            ]);

        $this->actingAs($eventAdmin)
            ->patch(route('admin.registrations.verify-payment', $registrations[1]), [
                'action' => 'reject',
                'admin_notes' => 'Invalid transaction ID'
            ]);

        // Approve registrations with verified payments
        $verifiedRegistrations = Registration::where('event_id', $event->id)
            ->where('payment_status', 'verified')->get();

        foreach ($verifiedRegistrations as $registration) {
            $this->actingAs($eventAdmin)
                ->patch(route('admin.registrations.approve', $registration));
        }

        // Export registration data
        $this->actingAs($eventAdmin)
            ->get(route('admin.registrations.export', $event))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Update event status to completed
        $this->actingAs($eventAdmin)
            ->patch(route('events.update', $event), [
                'status' => 'completed'
            ])
            ->assertRedirect();

        $event->refresh();
        $this->assertEquals('completed', $event->status);
    }

    /** @test */
    public function content_admin_can_manage_content_and_prayer_times()
    {
        $contentAdmin = $this->createContentAdmin();

        // Create blog post
        $response = $this->actingAs($contentAdmin)
            ->post(route('blogs.store'), [
                'title' => 'Islamic Values in Technology',
                'content' => 'A comprehensive article about integrating Islamic values in modern technology...',
                'image' => UploadedFile::fake()->image('blog_image.jpg')
            ]);

        $response->assertRedirect();
        $blog = Blog::where('title', 'Islamic Values in Technology')->first();
        $this->assertNotNull($blog);
        $this->assertEquals($contentAdmin->id, $blog->author_id);

        // Update prayer times
        $response = $this->actingAs($contentAdmin)
            ->post(route('admin.prayer-times.update'), [
                'date' => now()->toDateString(),
                'fajr' => '05:30',
                'dhuhr' => '12:15',
                'asr' => '15:45',
                'maghrib' => '18:20',
                'isha' => '19:45',
                'location' => 'IOT Masjid'
            ]);

        $response->assertRedirect();
        $prayerTime = PrayerTime::where('date', now()->toDateString())->first();
        $this->assertNotNull($prayerTime);
        $this->assertEquals($contentAdmin->id, $prayerTime->updated_by);

        // Bulk update prayer times for the week
        $dates = [];
        $times = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i)->toDateString();
            $dates[] = $date;
            $times[$date] = [
                'fajr' => '05:30',
                'dhuhr' => '12:15',
                'asr' => '15:45',
                'maghrib' => '18:20',
                'isha' => '19:45'
            ];
        }

        $this->actingAs($contentAdmin)
            ->post(route('admin.prayer-times.bulk-update'), [
                'dates' => $dates,
                'times' => $times
            ])
            ->assertRedirect();

        $this->assertEquals(7, PrayerTime::whereIn('date', $dates)->count());

        // Upload general gallery images
        $this->actingAs($contentAdmin)
            ->post(route('gallery.upload'), [
                'images' => [
                    UploadedFile::fake()->image('masjid_photo.jpg'),
                    UploadedFile::fake()->image('campus_photo.jpg')
                ],
                'captions' => ['IOT Masjid', 'Campus view']
            ])
            ->assertRedirect();

        $this->assertEquals(2, GalleryImage::whereNull('imageable_type')->count());
    }

    /** @test */
    public function admin_dashboard_shows_comprehensive_statistics()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Create test data
        $fest = Fest::factory()->create(['created_by' => $superAdmin->id]);
        $events = Event::factory()->count(3)->create(['fest_id' => $fest->id]);
        
        foreach ($events as $event) {
            Registration::factory()->count(5)->create([
                'event_id' => $event->id,
                'status' => 'approved'
            ]);
            Registration::factory()->count(2)->create([
                'event_id' => $event->id,
                'status' => 'pending'
            ]);
        }

        Blog::factory()->count(4)->create(['author_id' => $superAdmin->id]);
        User::factory()->count(10)->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['total_events']);
        $this->assertEquals(15, $stats['total_registrations']);
        $this->assertEquals(6, $stats['pending_registrations']);
        $this->assertEquals(4, $stats['total_blogs']);
        $this->assertEquals(11, $stats['total_users']); // Including the admin
    }

    /** @test */
    public function admin_can_manage_user_roles_and_permissions()
    {
        $superAdmin = $this->createSuperAdmin();
        $users = User::factory()->count(3)->create(['role' => 'member']);

        // View user management page
        $response = $this->actingAs($superAdmin)
            ->get(route('admin.user-management'));

        $response->assertOk();
        $response->assertSee($users[0]->name);

        // Update user role
        $this->actingAs($superAdmin)
            ->patch(route('admin.users.update-role', $users[0]), [
                'role' => 'event_admin'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $users[0]->refresh();
        $this->assertEquals('event_admin', $users[0]->role);

        // Lock user account
        $this->actingAs($superAdmin)
            ->patch(route('admin.users.lock', $users[1]), [
                'reason' => 'Suspicious activity'
            ])
            ->assertRedirect();

        $users[1]->refresh();
        $this->assertNotNull($users[1]->locked_at);
        $this->assertEquals('Suspicious activity', $users[1]->lock_reason);

        // Unlock user account
        $this->actingAs($superAdmin)
            ->patch(route('admin.users.unlock', $users[1]))
            ->assertRedirect();

        $users[1]->refresh();
        $this->assertNull($users[1]->locked_at);
    }

    /** @test */
    public function admin_can_view_system_analytics()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Create data for analytics
        $fest = Fest::factory()->create();
        $events = Event::factory()->count(5)->create(['fest_id' => $fest->id]);
        
        // Create registrations with different statuses
        foreach ($events as $event) {
            Registration::factory()->count(3)->create([
                'event_id' => $event->id,
                'status' => 'approved',
                'registered_at' => now()->subDays(rand(1, 30))
            ]);
        }

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.analytics'));

        $response->assertOk();
        $response->assertViewHas(['registrationTrends', 'popularEvents', 'userGrowth']);
    }

    /** @test */
    public function role_based_access_control_is_enforced()
    {
        $member = $this->createMember();
        $contentAdmin = $this->createContentAdmin();
        $eventAdmin = $this->createEventAdmin();
        $superAdmin = $this->createSuperAdmin();

        $fest = Fest::factory()->create();
        $event = Event::factory()->create();
        $registration = Registration::factory()->create();
        $user = User::factory()->create();

        // Test member access (should be denied for admin functions)
        $this->actingAs($member)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        // Test content admin access
        $this->actingAs($contentAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($contentAdmin)
            ->get(route('admin.registrations.index'))
            ->assertForbidden();

        // Test event admin access
        $this->actingAs($eventAdmin)
            ->get(route('admin.registrations.index'))
            ->assertOk();

        $this->actingAs($eventAdmin)
            ->get(route('admin.user-management'))
            ->assertForbidden();

        // Test super admin access (should have access to everything)
        $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('admin.registrations.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('admin.user-management'))
            ->assertOk();
    }
}