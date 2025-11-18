<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Registration;
use App\Services\RegistrationService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the NotificationService to prevent actual emails during testing
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('sendRegistrationConfirmation')->andReturn(true);
            $mock->shouldReceive('sendRegistrationCancellation')->andReturn(true);
        });
    }

    /** @test */
    public function authenticated_user_can_view_individual_registration_form()
    {
        $user = User::factory()->create();
        $fest = Fest::factory()->create();
        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'registration_type' => 'individual',
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.individual', $event));

        $response->assertStatus(200);
        $response->assertViewIs('registrations.individual');
        $response->assertViewHas('event', $event);
    }

    /** @test */
    public function user_cannot_register_for_event_that_does_not_allow_individual_registration()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.individual', $event));

        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('error', 'This event does not allow individual registration.');
    }

    /** @test */
    public function user_can_register_individually_for_free_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
            'status' => 'published',
            'fee_amount' => 0,
            'event_date' => now()->addDays(7),
        ]);

        $registrationData = [
            'individual_name' => $user->name,
        ];

        $response = $this->actingAs($user)
                         ->post(route('registrations.individual.store', $event), $registrationData);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_type' => 'individual',
            'individual_name' => $user->name,
            'payment_required' => false,
            'status' => 'pending',
        ]);

        $registration = Registration::where('event_id', $event->id)
                                  ->where('user_id', $user->id)
                                  ->first();

        $response->assertRedirect(route('registrations.show', $registration));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function user_can_register_individually_for_paid_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
            'status' => 'published',
            'fee_amount' => 100,
            'event_date' => now()->addDays(7),
        ]);

        $registrationData = [
            'individual_name' => $user->name,
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($user)
                         ->post(route('registrations.individual.store', $event), $registrationData);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_type' => 'individual',
            'individual_name' => $user->name,
            'payment_required' => true,
            'payment_amount' => 100,
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $registration = Registration::where('event_id', $event->id)
                                  ->where('user_id', $user->id)
                                  ->first();

        $response->assertRedirect(route('registrations.show', $registration));
    }

    /** @test */
    public function user_cannot_register_twice_for_same_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        // Create existing registration
        Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.individual', $event));

        $response->assertRedirect();
        $response->assertSessionHas('info', 'You are already registered for this event.');
    }

    /** @test */
    public function user_can_view_their_registration_details()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.show', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('registrations.show');
        $response->assertViewHas('registration', $registration);
    }

    /** @test */
    public function user_cannot_view_other_users_registration_details()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->create();
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.show', $registration));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_their_registration_history()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        
        $registration1 = Registration::factory()->create([
            'event_id' => $event1->id,
            'user_id' => $user->id,
        ]);
        
        $registration2 = Registration::factory()->create([
            'event_id' => $event2->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('registrations.history'));

        $response->assertStatus(200);
        $response->assertViewIs('registrations.history');
        $response->assertViewHas('registrations');
        
        // Check that both registrations are in the view
        $viewRegistrations = $response->viewData('registrations');
        $this->assertTrue($viewRegistrations->contains($registration1));
        $this->assertTrue($viewRegistrations->contains($registration2));
    }

    /** @test */
    public function user_can_cancel_their_pending_registration()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'event_date' => now()->addDays(7), // Event is more than 24 hours away
        ]);
        
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
                         ->patch(route('registrations.cancel', $registration));

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'cancelled',
        ]);

        $response->assertRedirect(route('registrations.history'));
        $response->assertSessionHas('success', 'Registration cancelled successfully.');
    }

    /** @test */
    public function user_cannot_cancel_registration_close_to_event_date()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'event_date' => now()->addHours(12), // Event is within 24 hours
        ]);
        
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
                         ->patch(route('registrations.cancel', $registration));

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'pending', // Status should remain unchanged
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This registration cannot be cancelled at this time.');
    }

    /** @test */
    public function guest_user_cannot_access_registration_pages()
    {
        $event = Event::factory()->create();
        $registration = Registration::factory()->create();

        // Test individual registration form
        $response = $this->get(route('registrations.individual', $event));
        $response->assertRedirect(route('login'));

        // Test registration history
        $response = $this->get(route('registrations.history'));
        $response->assertRedirect(route('login'));

        // Test registration details
        $response = $this->get(route('registrations.show', $registration));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function registration_validation_works_correctly()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
            'status' => 'published',
            'fee_amount' => 100, // Paid event
            'event_date' => now()->addDays(7),
        ]);

        // Test missing required fields for paid event
        $response = $this->actingAs($user)
                         ->post(route('registrations.individual.store', $event), [
                             'individual_name' => $user->name,
                             // Missing payment fields
                         ]);

        $response->assertSessionHasErrors(['payment_method', 'transaction_id', 'payment_date']);

        // Test invalid payment method
        $response = $this->actingAs($user)
                         ->post(route('registrations.individual.store', $event), [
                             'individual_name' => $user->name,
                             'payment_method' => 'invalid_method',
                             'transaction_id' => 'TXN123',
                             'payment_date' => now()->toDateString(),
                         ]);

        $response->assertSessionHasErrors(['payment_method']);

        // Test future payment date
        $response = $this->actingAs($user)
                         ->post(route('registrations.individual.store', $event), [
                             'individual_name' => $user->name,
                             'payment_method' => 'bkash',
                             'transaction_id' => 'TXN123',
                             'payment_date' => now()->addDay()->toDateString(),
                         ]);

        $response->assertSessionHasErrors(['payment_date']);
    }
}