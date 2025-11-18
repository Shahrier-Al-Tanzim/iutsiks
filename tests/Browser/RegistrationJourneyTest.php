<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegistrationJourneyTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test complete individual registration journey
     */
    public function test_user_can_complete_individual_registration_journey()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'member'
        ]);

        $fest = Fest::factory()->create([
            'title' => 'Tech Fest 2024',
            'status' => 'published'
        ]);

        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'title' => 'Programming Contest',
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 100,
            'max_participants' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Log in')
                    ->assertPathIs('/dashboard');

            // Navigate to events
            $browser->visit('/events')
                    ->assertSee('Programming Contest')
                    ->click('@event-' . $event->id)
                    ->assertPathIs('/events/' . $event->id)
                    ->assertSee('Programming Contest')
                    ->assertSee('Registration Fee: $100')
                    ->assertSee('Register Now');

            // Start registration
            $browser->click('@register-individual-btn')
                    ->assertPathIs('/events/' . $event->id . '/register/individual')
                    ->assertSee('Individual Registration')
                    ->type('individual_name', 'John Doe')
                    ->select('payment_method', 'bkash')
                    ->type('transaction_id', 'TXN123456789')
                    ->type('payment_date', now()->toDateString())
                    ->press('Submit Registration')
                    ->assertPathIs('/registrations/confirmation')
                    ->assertSee('Registration Submitted Successfully')
                    ->assertSee('Your registration is pending approval');

            // Check registration history
            $browser->visit('/registrations/history')
                    ->assertSee('Programming Contest')
                    ->assertSee('Pending')
                    ->assertSee('Payment Pending');
        });
    }

    /**
     * Test team registration journey
     */
    public function test_user_can_complete_team_registration_journey()
    {
        $leader = User::factory()->create([
            'name' => 'Team Leader',
            'email' => 'leader@example.com',
            'role' => 'member'
        ]);

        $member1 = User::factory()->create([
            'name' => 'Member One',
            'email' => 'member1@example.com'
        ]);

        $member2 = User::factory()->create([
            'name' => 'Member Two',
            'email' => 'member2@example.com'
        ]);

        $event = Event::factory()->create([
            'title' => 'Quiz Competition',
            'status' => 'published',
            'registration_type' => 'team',
            'fee_amount' => 0,
            'max_participants' => 20,
            'registration_deadline' => now()->addWeek()
        ]);

        $this->browse(function (Browser $browser) use ($leader, $member1, $member2, $event) {
            $browser->loginAs($leader)
                    ->visit('/events/' . $event->id)
                    ->assertSee('Quiz Competition')
                    ->assertSee('Team Registration')
                    ->click('@register-team-btn')
                    ->assertPathIs('/events/' . $event->id . '/register/team')
                    ->type('team_name', 'Dream Team')
                    ->select('team_members[]', $member1->id)
                    ->select('team_members[]', $member2->id)
                    ->press('Submit Team Registration')
                    ->assertPathIs('/registrations/confirmation')
                    ->assertSee('Team Registration Submitted')
                    ->assertSee('Dream Team');

            // Verify team registration in history
            $browser->visit('/registrations/history')
                    ->assertSee('Quiz Competition')
                    ->assertSee('Dream Team')
                    ->assertSee('Team Registration');
        });
    }

    /**
     * Test registration validation and error handling
     */
    public function test_registration_validation_works_correctly()
    {
        $user = User::factory()->create();
        
        $event = Event::factory()->create([
            'title' => 'Validation Test Event',
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->loginAs($user)
                    ->visit('/events/' . $event->id . '/register/individual')
                    ->press('Submit Registration')
                    ->assertSee('The individual name field is required')
                    ->assertSee('The payment method field is required')
                    ->assertSee('The transaction id field is required');

            // Fill form with invalid data
            $browser->type('individual_name', '')
                    ->select('payment_method', 'bkash')
                    ->type('transaction_id', '')
                    ->type('payment_date', 'invalid-date')
                    ->press('Submit Registration')
                    ->assertSee('The individual name field is required')
                    ->assertSee('The transaction id field is required');

            // Fill form correctly
            $browser->type('individual_name', 'Valid Name')
                    ->type('transaction_id', 'VALID123')
                    ->type('payment_date', now()->toDateString())
                    ->press('Submit Registration')
                    ->assertPathIs('/registrations/confirmation');
        });
    }

    /**
     * Test event capacity limits
     */
    public function test_registration_blocked_when_event_full()
    {
        $user = User::factory()->create();
        
        $event = Event::factory()->create([
            'title' => 'Full Event',
            'status' => 'published',
            'registration_type' => 'individual',
            'max_participants' => 1,
            'fee_amount' => 0
        ]);

        // Fill up the event
        $otherUser = User::factory()->create();
        $event->registrations()->create([
            'user_id' => $otherUser->id,
            'registration_type' => 'individual',
            'individual_name' => 'Other User',
            'status' => 'approved',
            'payment_required' => false
        ]);

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->loginAs($user)
                    ->visit('/events/' . $event->id)
                    ->assertSee('Full Event')
                    ->assertSee('Event Full')
                    ->assertDontSee('Register Now');
        });
    }

    /**
     * Test registration deadline enforcement
     */
    public function test_registration_blocked_after_deadline()
    {
        $user = User::factory()->create();
        
        $event = Event::factory()->create([
            'title' => 'Expired Event',
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => now()->subDay()
        ]);

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->loginAs($user)
                    ->visit('/events/' . $event->id)
                    ->assertSee('Expired Event')
                    ->assertSee('Registration Closed')
                    ->assertDontSee('Register Now');
        });
    }

    /**
     * Test guest user redirection to login
     */
    public function test_guest_redirected_to_login_for_registration()
    {
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual'
        ]);

        $this->browse(function (Browser $browser) use ($event) {
            $browser->visit('/events/' . $event->id)
                    ->assertSee('Login to Register')
                    ->click('@login-to-register-btn')
                    ->assertPathIs('/login');
        });
    }

    /**
     * Test responsive design on mobile
     */
    public function test_registration_works_on_mobile()
    {
        $user = User::factory()->create();
        
        $event = Event::factory()->create([
            'title' => 'Mobile Test Event',
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 0
        ]);

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->resize(375, 667) // iPhone SE dimensions
                    ->loginAs($user)
                    ->visit('/events/' . $event->id)
                    ->assertSee('Mobile Test Event')
                    ->click('@register-individual-btn')
                    ->type('individual_name', 'Mobile User')
                    ->press('Submit Registration')
                    ->assertPathIs('/registrations/confirmation')
                    ->assertSee('Registration Submitted Successfully');
        });
    }

    /**
     * Test navigation and user experience flow
     */
    public function test_complete_user_navigation_flow()
    {
        $user = User::factory()->create();
        
        $fest = Fest::factory()->create([
            'title' => 'Navigation Test Fest',
            'status' => 'published'
        ]);

        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'title' => 'Navigation Test Event',
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 0
        ]);

        $this->browse(function (Browser $browser) use ($user, $fest, $event) {
            // Start from home page
            $browser->visit('/')
                    ->assertSee('Islamic Society')
                    ->click('@login-link')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Log in')
                    ->assertPathIs('/dashboard');

            // Navigate to events
            $browser->click('@events-nav-link')
                    ->assertPathIs('/events')
                    ->assertSee('Navigation Test Event')
                    ->click('@event-' . $event->id)
                    ->assertPathIs('/events/' . $event->id);

            // Register for event
            $browser->click('@register-individual-btn')
                    ->type('individual_name', $user->name)
                    ->press('Submit Registration')
                    ->assertPathIs('/registrations/confirmation');

            // Check registration history
            $browser->click('@my-registrations-link')
                    ->assertPathIs('/registrations/history')
                    ->assertSee('Navigation Test Event')
                    ->assertSee('Pending');

            // Navigate to profile
            $browser->click('@profile-link')
                    ->assertPathIs('/profile')
                    ->assertSee($user->name)
                    ->assertSee($user->email);
        });
    }
}