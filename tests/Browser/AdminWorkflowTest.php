<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use App\Models\Registration;
use App\Models\PrayerTime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin dashboard access and overview
     */
    public function test_admin_can_access_dashboard_and_view_statistics()
    {
        $admin = User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com'
        ]);

        // Create some test data
        $fest = Fest::factory()->create(['created_by' => $admin->id]);
        $events = Event::factory()->count(3)->create(['fest_id' => $fest->id]);
        
        foreach ($events as $event) {
            Registration::factory()->count(2)->create([
                'event_id' => $event->id,
                'status' => 'approved'
            ]);
            Registration::factory()->create([
                'event_id' => $event->id,
                'status' => 'pending'
            ]);
        }

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->assertSee('Admin Dashboard')
                    ->assertSee('Total Events')
                    ->assertSee('Total Registrations')
                    ->assertSee('Pending Registrations')
                    ->assertSee('3') // Total events
                    ->assertSee('9') // Total registrations (6 approved + 3 pending)
                    ->assertSee('3'); // Pending registrations
        });
    }

    /**
     * Test fest creation workflow
     */
    public function test_admin_can_create_fest_with_events()
    {
        $admin = User::factory()->superAdmin()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->click('@create-fest-btn')
                    ->assertPathIs('/fests/create')
                    ->type('title', 'Annual Tech Fest 2024')
                    ->type('description', 'A comprehensive technology festival featuring various competitions and workshops.')
                    ->type('start_date', now()->addMonth()->toDateString())
                    ->type('end_date', now()->addMonth()->addDays(3)->toDateString())
                    ->press('Create Fest')
                    ->assertPathIs('/fests/*')
                    ->assertSee('Annual Tech Fest 2024')
                    ->assertSee('Fest created successfully');

            // Add event to the fest
            $browser->click('@add-event-btn')
                    ->assertSee('Create Event')
                    ->type('title', 'Programming Contest')
                    ->type('description', 'Competitive programming event')
                    ->type('event_date', now()->addMonth()->addDay()->toDateString())
                    ->type('event_time', '10:00')
                    ->select('type', 'competition')
                    ->select('registration_type', 'team')
                    ->type('location', 'Main Auditorium')
                    ->type('max_participants', '50')
                    ->type('fee_amount', '200')
                    ->type('registration_deadline', now()->addMonth()->toDateString())
                    ->select('status', 'published')
                    ->press('Create Event')
                    ->assertSee('Event created successfully')
                    ->assertSee('Programming Contest');
        });
    }

    /**
     * Test registration management workflow
     */
    public function test_admin_can_manage_registrations()
    {
        $admin = User::factory()->eventAdmin()->create();
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $event = Event::factory()->create([
            'title' => 'Test Event',
            'fee_amount' => 100
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'individual_name' => 'John Doe',
            'payment_required' => true,
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_status' => 'pending',
            'status' => 'pending'
        ]);

        $this->browse(function (Browser $browser) use ($admin, $registration) {
            $browser->loginAs($admin)
                    ->visit('/admin/registrations')
                    ->assertSee('Registration Management')
                    ->assertSee('John Doe')
                    ->assertSee('Test Event')
                    ->assertSee('Pending')
                    ->click('@view-registration-' . $registration->id)
                    ->assertSee('Registration Details')
                    ->assertSee('TXN123456');

            // Verify payment
            $browser->click('@verify-payment-btn')
                    ->select('action', 'approve')
                    ->type('admin_notes', 'Payment verified successfully')
                    ->press('Update Payment Status')
                    ->assertSee('Payment status updated')
                    ->assertSee('Verified');

            // Approve registration
            $browser->click('@approve-registration-btn')
                    ->press('Approve Registration')
                    ->assertSee('Registration approved')
                    ->assertSee('Approved');
        });
    }

    /**
     * Test payment rejection and resubmission workflow
     */
    public function test_admin_can_reject_payment_and_user_can_resubmit()
    {
        $admin = User::factory()->eventAdmin()->create();
        $user = User::factory()->create();
        
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_method' => 'bkash',
            'transaction_id' => 'INVALID123',
            'payment_status' => 'pending',
            'status' => 'pending'
        ]);

        $this->browse(function (Browser $browser) use ($admin, $user, $registration) {
            // Admin rejects payment
            $browser->loginAs($admin)
                    ->visit('/admin/registrations/' . $registration->id)
                    ->click('@verify-payment-btn')
                    ->select('action', 'reject')
                    ->type('admin_notes', 'Invalid transaction ID provided')
                    ->press('Update Payment Status')
                    ->assertSee('Payment status updated')
                    ->assertSee('Rejected');

            // User resubmits payment
            $browser->loginAs($user)
                    ->visit('/registrations/history')
                    ->assertSee('Payment Rejected')
                    ->click('@resubmit-payment-' . $registration->id)
                    ->select('payment_method', 'nagad')
                    ->type('transaction_id', 'VALID789')
                    ->type('payment_date', now()->toDateString())
                    ->press('Resubmit Payment')
                    ->assertSee('Payment resubmitted')
                    ->assertSee('Payment Pending');
        });
    }

    /**
     * Test prayer times management
     */
    public function test_content_admin_can_manage_prayer_times()
    {
        $admin = User::factory()->contentAdmin()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->click('@manage-prayer-times-btn')
                    ->assertPathIs('/admin/prayer-times')
                    ->assertSee('Prayer Times Management');

            // Update today's prayer times
            $browser->click('@update-today-btn')
                    ->type('fajr', '05:30')
                    ->type('dhuhr', '12:15')
                    ->type('asr', '15:45')
                    ->type('maghrib', '18:30')
                    ->type('isha', '20:00')
                    ->type('location', 'IOT Masjid')
                    ->type('notes', 'Updated prayer times for today')
                    ->press('Update Prayer Times')
                    ->assertSee('Prayer times updated successfully')
                    ->assertSee('05:30')
                    ->assertSee('12:15');

            // Bulk update for the week
            $browser->click('@bulk-update-btn')
                    ->assertSee('Bulk Update Prayer Times')
                    ->check('dates[]', now()->addDay()->toDateString())
                    ->check('dates[]', now()->addDays(2)->toDateString())
                    ->type('bulk_fajr', '05:31')
                    ->type('bulk_dhuhr', '12:16')
                    ->type('bulk_asr', '15:46')
                    ->type('bulk_maghrib', '18:31')
                    ->type('bulk_isha', '20:01')
                    ->press('Bulk Update')
                    ->assertSee('Prayer times updated for selected dates');
        });
    }

    /**
     * Test user management workflow
     */
    public function test_super_admin_can_manage_users()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'name' => 'Regular Member',
            'role' => 'member'
        ]);

        $this->browse(function (Browser $browser) use ($superAdmin, $member) {
            $browser->loginAs($superAdmin)
                    ->visit('/admin/user-management')
                    ->assertSee('User Management')
                    ->assertSee('Regular Member')
                    ->assertSee('Member');

            // Update user role
            $browser->click('@edit-user-' . $member->id)
                    ->select('role', 'event_admin')
                    ->press('Update Role')
                    ->assertSee('User role updated')
                    ->assertSee('Event Admin');

            // Lock user account
            $browser->click('@lock-user-' . $member->id)
                    ->type('lock_reason', 'Suspicious activity detected')
                    ->press('Lock Account')
                    ->assertSee('User account locked')
                    ->assertSee('Locked');

            // Unlock user account
            $browser->click('@unlock-user-' . $member->id)
                    ->press('Unlock Account')
                    ->assertSee('User account unlocked')
                    ->assertDontSee('Locked');
        });
    }

    /**
     * Test analytics and reporting
     */
    public function test_admin_can_view_analytics_and_export_data()
    {
        $admin = User::factory()->superAdmin()->create();
        
        $event = Event::factory()->create(['title' => 'Analytics Test Event']);
        Registration::factory()->count(5)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);

        $this->browse(function (Browser $browser) use ($admin, $event) {
            $browser->loginAs($admin)
                    ->visit('/admin/analytics')
                    ->assertSee('System Analytics')
                    ->assertSee('Registration Trends')
                    ->assertSee('Popular Events')
                    ->assertSee('User Growth');

            // Export registration data
            $browser->visit('/admin/registrations')
                    ->click('@export-event-' . $event->id)
                    ->assertSee('Export started'); // Assuming there's a success message
        });
    }

    /**
     * Test role-based access control in browser
     */
    public function test_role_based_access_control_in_browser()
    {
        $member = User::factory()->create(['role' => 'member']);
        $contentAdmin = User::factory()->contentAdmin()->create();
        $eventAdmin = User::factory()->eventAdmin()->create();

        $this->browse(function (Browser $browser) use ($member, $contentAdmin, $eventAdmin) {
            // Member should not access admin areas
            $browser->loginAs($member)
                    ->visit('/admin/dashboard')
                    ->assertSee('403')
                    ->orAssertPathIs('/login');

            // Content admin should access dashboard but not registrations
            $browser->loginAs($contentAdmin)
                    ->visit('/admin/dashboard')
                    ->assertSee('Admin Dashboard')
                    ->visit('/admin/registrations')
                    ->assertSee('403');

            // Event admin should access registrations but not user management
            $browser->loginAs($eventAdmin)
                    ->visit('/admin/registrations')
                    ->assertSee('Registration Management')
                    ->visit('/admin/user-management')
                    ->assertSee('403');
        });
    }

    /**
     * Test responsive admin interface
     */
    public function test_admin_interface_works_on_mobile()
    {
        $admin = User::factory()->superAdmin()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->resize(375, 667) // iPhone SE dimensions
                    ->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->assertSee('Admin Dashboard')
                    ->click('@mobile-menu-toggle')
                    ->assertVisible('@mobile-nav-menu')
                    ->click('@mobile-registrations-link')
                    ->assertPathIs('/admin/registrations')
                    ->assertSee('Registration Management');
        });
    }
}