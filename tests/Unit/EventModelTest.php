<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\User;
use App\Models\Fest;
use App\Models\Registration;
use App\Models\GalleryImage;
use Tests\TestCase;
use Carbon\Carbon;

class EventModelTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $event = new Event();
        $expected = [
            'fest_id', 'title', 'description', 'event_date', 'event_time',
            'type', 'registration_type', 'location', 'max_participants',
            'fee_amount', 'registration_deadline', 'status', 'author_id', 'image'
        ];
        
        $this->assertEquals($expected, $event->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $event = Event::factory()->create([
            'event_date' => '2024-12-25',
            'event_time' => '2024-12-25 10:00:00',
            'registration_deadline' => '2024-12-20 23:59:59',
            'fee_amount' => 100.50,
            'max_participants' => 50
        ]);
        
        $this->assertInstanceOf(Carbon::class, $event->event_date);
        $this->assertInstanceOf(Carbon::class, $event->event_time);
        $this->assertInstanceOf(Carbon::class, $event->registration_deadline);
        $this->assertEquals('100.50', $event->fee_amount);
        $this->assertIsInt($event->max_participants);
    }

    /** @test */
    public function it_belongs_to_author()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['author_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $event->author);
        $this->assertEquals($user->id, $event->author->id);
    }

    /** @test */
    public function it_belongs_to_fest()
    {
        $fest = Fest::factory()->create();
        $event = Event::factory()->create(['fest_id' => $fest->id]);
        
        $this->assertInstanceOf(Fest::class, $event->fest);
        $this->assertEquals($fest->id, $event->fest->id);
    }

    /** @test */
    public function it_can_have_no_fest()
    {
        $event = Event::factory()->create(['fest_id' => null]);
        
        $this->assertNull($event->fest);
    }

    /** @test */
    public function it_has_many_registrations()
    {
        $event = Event::factory()->create();
        Registration::factory()->count(3)->create(['event_id' => $event->id]);
        
        $this->assertCount(3, $event->registrations);
        $this->assertInstanceOf(Registration::class, $event->registrations->first());
    }

    /** @test */
    public function it_has_many_gallery_images()
    {
        $event = Event::factory()->create();
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id
        ]);
        
        $this->assertCount(2, $event->gallery);
        $this->assertInstanceOf(GalleryImage::class, $event->gallery->first());
    }

    /** @test */
    public function it_gets_approved_registrations()
    {
        $event = Event::factory()->create();
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'approved']);
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'pending']);
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'rejected']);
        
        $this->assertCount(1, $event->approvedRegistrations);
    }

    /** @test */
    public function it_gets_pending_registrations()
    {
        $event = Event::factory()->create();
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'approved']);
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'pending']);
        Registration::factory()->create(['event_id' => $event->id, 'status' => 'pending']);
        
        $this->assertCount(2, $event->pendingRegistrations);
    }

    /** @test */
    public function it_checks_if_registration_is_open()
    {
        // Published event with no deadline and no capacity limit
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => null,
            'max_participants' => null
        ]);
        
        $this->assertTrue($event->isRegistrationOpen());
    }

    /** @test */
    public function it_closes_registration_for_on_spot_events()
    {
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'on_spot'
        ]);
        
        $this->assertFalse($event->isRegistrationOpen());
    }

    /** @test */
    public function it_closes_registration_after_deadline()
    {
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => now()->subDay()
        ]);
        
        $this->assertFalse($event->isRegistrationOpen());
    }

    /** @test */
    public function it_closes_registration_when_full()
    {
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'max_participants' => 2
        ]);
        
        Registration::factory()->count(2)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);
        
        $this->assertFalse($event->isRegistrationOpen());
    }

    /** @test */
    public function it_closes_registration_for_draft_events()
    {
        $event = Event::factory()->create([
            'status' => 'draft',
            'registration_type' => 'individual'
        ]);
        
        $this->assertFalse($event->isRegistrationOpen());
    }

    /** @test */
    public function it_gets_registered_count()
    {
        $event = Event::factory()->create();
        Registration::factory()->count(3)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);
        Registration::factory()->create([
            'event_id' => $event->id,
            'status' => 'pending'
        ]);
        
        $this->assertEquals(3, $event->getRegisteredCount());
    }

    /** @test */
    public function it_gets_available_spots()
    {
        $event = Event::factory()->create(['max_participants' => 10]);
        Registration::factory()->count(3)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);
        
        $this->assertEquals(7, $event->getAvailableSpots());
    }

    /** @test */
    public function it_returns_null_for_unlimited_events()
    {
        $event = Event::factory()->create(['max_participants' => null]);
        
        $this->assertNull($event->getAvailableSpots());
    }

    /** @test */
    public function it_checks_if_event_is_full()
    {
        $event = Event::factory()->create(['max_participants' => 2]);
        Registration::factory()->count(2)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);
        
        $this->assertTrue($event->isFull());
    }

    /** @test */
    public function it_checks_if_unlimited_event_is_not_full()
    {
        $event = Event::factory()->create(['max_participants' => null]);
        Registration::factory()->count(100)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);
        
        $this->assertFalse($event->isFull());
    }

    /** @test */
    public function it_checks_if_event_requires_payment()
    {
        $paidEvent = Event::factory()->create(['fee_amount' => 100]);
        $freeEvent = Event::factory()->create(['fee_amount' => 0]);
        
        $this->assertTrue($paidEvent->requiresPayment());
        $this->assertFalse($freeEvent->requiresPayment());
    }

    /** @test */
    public function it_checks_individual_registration_allowance()
    {
        $individualEvent = Event::factory()->create(['registration_type' => 'individual']);
        $teamEvent = Event::factory()->create(['registration_type' => 'team']);
        $bothEvent = Event::factory()->create(['registration_type' => 'both']);
        $onSpotEvent = Event::factory()->create(['registration_type' => 'on_spot']);
        
        $this->assertTrue($individualEvent->allowsIndividualRegistration());
        $this->assertFalse($teamEvent->allowsIndividualRegistration());
        $this->assertTrue($bothEvent->allowsIndividualRegistration());
        $this->assertFalse($onSpotEvent->allowsIndividualRegistration());
    }

    /** @test */
    public function it_checks_team_registration_allowance()
    {
        $individualEvent = Event::factory()->create(['registration_type' => 'individual']);
        $teamEvent = Event::factory()->create(['registration_type' => 'team']);
        $bothEvent = Event::factory()->create(['registration_type' => 'both']);
        $onSpotEvent = Event::factory()->create(['registration_type' => 'on_spot']);
        
        $this->assertFalse($individualEvent->allowsTeamRegistration());
        $this->assertTrue($teamEvent->allowsTeamRegistration());
        $this->assertTrue($bothEvent->allowsTeamRegistration());
        $this->assertFalse($onSpotEvent->allowsTeamRegistration());
    }

    /** @test */
    public function it_checks_if_event_is_upcoming()
    {
        $upcomingEvent = Event::factory()->create(['event_date' => now()->addDay()]);
        $pastEvent = Event::factory()->create(['event_date' => now()->subDay()]);
        
        $this->assertTrue($upcomingEvent->isUpcoming());
        $this->assertFalse($pastEvent->isUpcoming());
    }

    /** @test */
    public function it_checks_if_event_is_today()
    {
        $todayEvent = Event::factory()->create(['event_date' => now()->toDateString()]);
        $tomorrowEvent = Event::factory()->create(['event_date' => now()->addDay()]);
        
        $this->assertTrue($todayEvent->isToday());
        $this->assertFalse($tomorrowEvent->isToday());
    }

    /** @test */
    public function it_checks_if_event_is_completed()
    {
        $pastEvent = Event::factory()->create(['event_date' => now()->subDay()->toDateString()]);
        $completedEvent = Event::factory()->create(['status' => 'completed']);
        $upcomingEvent = Event::factory()->create(['event_date' => now()->addDays(2)->toDateString()]);
        
        $this->assertTrue($pastEvent->isCompleted());
        $this->assertTrue($completedEvent->isCompleted());
        $this->assertFalse($upcomingEvent->isCompleted());
    }

    /** @test */
    public function it_scopes_published_events()
    {
        Event::factory()->create(['status' => 'published']);
        Event::factory()->create(['status' => 'draft']);
        Event::factory()->create(['status' => 'completed']);
        
        $publishedEvents = Event::published()->get();
        
        $this->assertCount(1, $publishedEvents);
        $this->assertEquals('published', $publishedEvents->first()->status);
    }

    /** @test */
    public function it_scopes_upcoming_events()
    {
        Event::factory()->create(['event_date' => now()->addDay()]);
        Event::factory()->create(['event_date' => now()->subDay()]);
        
        $upcomingEvents = Event::upcoming()->get();
        
        $this->assertCount(1, $upcomingEvents);
    }

    /** @test */
    public function it_scopes_events_by_type()
    {
        Event::factory()->create(['type' => 'quiz']);
        Event::factory()->create(['type' => 'lecture']);
        Event::factory()->create(['type' => 'quiz']);
        
        $quizEvents = Event::byType('quiz')->get();
        
        $this->assertCount(2, $quizEvents);
        $quizEvents->each(function ($event) {
            $this->assertEquals('quiz', $event->type);
        });
    }

    /** @test */
    public function it_scopes_events_with_open_registration()
    {
        // Event with open registration
        Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => now()->addWeek()
        ]);
        
        // On-spot event (should be excluded)
        Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'on_spot'
        ]);
        
        // Draft event (should be excluded)
        Event::factory()->create([
            'status' => 'draft',
            'registration_type' => 'individual'
        ]);
        
        // Expired deadline (should be excluded)
        Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => now()->subDay()
        ]);
        
        $openEvents = Event::withOpenRegistration()->get();
        
        $this->assertCount(1, $openEvents);
    }
}