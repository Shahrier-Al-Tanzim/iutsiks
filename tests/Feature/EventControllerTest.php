<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user with appropriate permissions
        $this->user = User::factory()->create([
            'role' => 'super_admin'
        ]);
    }

    public function test_can_view_events_index()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('events.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('events.index');
    }

    public function test_can_create_event_with_new_fields()
    {
        $this->actingAs($this->user);
        
        $fest = Fest::factory()->create();
        
        $eventData = [
            'fest_id' => $fest->id,
            'title' => 'Test Event',
            'description' => 'This is a test event description.',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'event_time' => '14:00',
            'type' => 'quiz',
            'registration_type' => 'individual',
            'location' => 'Main Auditorium',
            'max_participants' => 50,
            'fee_amount' => 100,
            'registration_deadline' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'published'
        ];
        
        $response = $this->post(route('events.store'), $eventData);
        
        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success', 'Event created successfully!');
        
        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'type' => 'quiz',
            'registration_type' => 'individual',
            'location' => 'Main Auditorium',
            'max_participants' => 50,
            'fee_amount' => 100,
            'status' => 'published'
        ]);
    }

    public function test_can_view_event_with_registration_info()
    {
        $this->actingAs($this->user);
        
        $event = Event::factory()->create([
            'type' => 'competition',
            'registration_type' => 'both',
            'max_participants' => 30,
            'fee_amount' => 50,
            'status' => 'published'
        ]);
        
        $response = $this->get(route('events.show', $event));
        
        $response->assertStatus(200);
        $response->assertViewIs('events.show');
        $response->assertViewHas('event', $event);
        $response->assertSee('Registration Information');
        $response->assertSee('Individual & Team');
        $response->assertSee('৳50.00');
    }

    public function test_can_update_event_with_new_fields()
    {
        $this->actingAs($this->user);
        
        $event = Event::factory()->create([
            'author_id' => $this->user->id
        ]);
        
        $updateData = [
            'title' => 'Updated Event Title',
            'description' => 'Updated description',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'event_time' => '16:00',
            'type' => 'workshop',
            'registration_type' => 'team',
            'location' => 'Conference Room',
            'max_participants' => 25,
            'fee_amount' => 75,
            'status' => 'published'
        ];
        
        $response = $this->put(route('events.update', $event), $updateData);
        
        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success', 'Event updated successfully!');
        
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title',
            'type' => 'workshop',
            'registration_type' => 'team',
            'location' => 'Conference Room',
            'max_participants' => 25,
            'fee_amount' => 75
        ]);
    }

    public function test_event_validation_rules()
    {
        $this->actingAs($this->user);
        
        // Test with invalid data
        $response = $this->post(route('events.store'), [
            'title' => '', // Required field empty
            'type' => 'invalid_type', // Invalid enum value
            'registration_type' => 'invalid_reg_type', // Invalid enum value
            'event_date' => 'invalid_date', // Invalid date
            'max_participants' => -1, // Negative number
            'fee_amount' => -50 // Negative fee
        ]);
        
        $response->assertSessionHasErrors([
            'title',
            'description',
            'type',
            'registration_type',
            'event_date'
        ]);
    }

    public function test_registration_status_display()
    {
        $this->actingAs($this->user);
        
        // Test on-spot registration event
        $onSpotEvent = Event::factory()->create([
            'registration_type' => 'on_spot',
            'status' => 'published'
        ]);
        
        $response = $this->get(route('events.show', $onSpotEvent));
        $response->assertSee('On-Spot Registration');
        
        // Test event with registration deadline passed
        $expiredEvent = Event::factory()->create([
            'registration_type' => 'individual',
            'registration_deadline' => now()->subDays(1),
            'status' => 'published'
        ]);
        
        $response = $this->get(route('events.show', $expiredEvent));
        $response->assertSee('Registration Closed');
    }
}