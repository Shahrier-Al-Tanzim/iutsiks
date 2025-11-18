<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Registration;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function admin_can_approve_payment()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'event_admin']);
        
        // Create regular user
        $user = User::factory()->create(['role' => 'member']);
        
        // Create fest and event
        $fest = Fest::factory()->create(['created_by' => $admin->id]);
        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'fee_amount' => 100.00,
            'author_id' => $admin->id,
        ]);
        
        // Create registration with pending payment
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_amount' => 100.00,
            'payment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->subDay(),
            'status' => 'pending',
        ]);

        // Admin approves payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.approve-payment', $registration), [
                'admin_notes' => 'Payment verified successfully'
            ]);

        $response->assertRedirect();
        
        // Assert payment status updated
        $registration->refresh();
        $this->assertEquals('verified', $registration->payment_status);
        $this->assertStringContainsString('Payment approved by', $registration->admin_notes);
        
        // Assert audit log created
        $this->assertDatabaseHas('registration_audit_logs', [
            'registration_id' => $registration->id,
            'admin_user_id' => $admin->id,
            'action' => 'payment_approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_payment()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'super_admin']);
        
        // Create regular user
        $user = User::factory()->create(['role' => 'member']);
        
        // Create fest and event
        $fest = Fest::factory()->create(['created_by' => $admin->id]);
        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'fee_amount' => 100.00,
            'author_id' => $admin->id,
        ]);
        
        // Create registration with pending payment
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_amount' => 100.00,
            'payment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->subDay(),
            'status' => 'pending',
        ]);

        // Admin rejects payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.reject-payment', $registration), [
                'rejection_reason' => 'Transaction ID not found in our records'
            ]);

        $response->assertRedirect();
        
        // Assert payment status updated
        $registration->refresh();
        $this->assertEquals('rejected', $registration->payment_status);
        $this->assertStringContainsString('Payment rejected by', $registration->admin_notes);
        
        // Assert audit log created
        $this->assertDatabaseHas('registration_audit_logs', [
            'registration_id' => $registration->id,
            'admin_user_id' => $admin->id,
            'action' => 'payment_rejected',
        ]);
    }

    /** @test */
    public function user_can_resubmit_rejected_payment()
    {
        // Create regular user
        $user = User::factory()->create(['role' => 'member']);
        
        // Create fest and event
        $fest = Fest::factory()->create();
        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'fee_amount' => 100.00,
            'registration_deadline' => now()->addWeek(),
            'event_date' => now()->addMonth(),
        ]);
        
        // Create registration with rejected payment
        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_amount' => 100.00,
            'payment_status' => 'rejected',
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->subDay(),
            'status' => 'pending',
        ]);

        // User resubmits payment
        $response = $this->actingAs($user)
            ->post(route('registrations.payment.resubmit.store', $registration), [
                'payment_method' => 'nagad',
                'transaction_id' => 'TXN789012',
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('registrations.show', $registration));
        
        // Assert payment details updated
        $registration->refresh();
        $this->assertEquals('pending', $registration->payment_status);
        $this->assertEquals('nagad', $registration->payment_method);
        $this->assertEquals('TXN789012', $registration->transaction_id);
        $this->assertStringContainsString('Payment details resubmitted by user', $registration->admin_notes);
    }

    /** @test */
    public function admin_can_bulk_approve_payments()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'event_admin']);
        
        // Create multiple registrations with pending payments
        $registrations = Registration::factory()->count(3)->create([
            'payment_required' => true,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $registrationIds = $registrations->pluck('id')->toArray();

        // Admin bulk approves payments
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.bulk-approve-payments'), [
                'registration_ids' => $registrationIds,
                'admin_notes' => 'Bulk payment approval'
            ]);

        $response->assertRedirect();
        
        // Assert all payments are verified
        foreach ($registrations as $registration) {
            $registration->refresh();
            $this->assertEquals('verified', $registration->payment_status);
            $this->assertStringContainsString('Payment bulk approved by', $registration->admin_notes);
        }
        
        // Assert audit logs created for all
        $this->assertEquals(3, \DB::table('registration_audit_logs')
            ->where('action', 'payment_bulk_approved')
            ->where('admin_user_id', $admin->id)
            ->count());
    }

    /** @test */
    public function non_admin_cannot_access_payment_verification()
    {
        // Create regular user
        $user = User::factory()->create(['role' => 'member']);
        
        // Try to access payment verification page
        $response = $this->actingAs($user)
            ->get(route('admin.registrations.payment-verification'));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_resubmit_verified_payment()
    {
        // Create regular user
        $user = User::factory()->create(['role' => 'member']);
        
        // Create registration with verified payment
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_status' => 'verified',
            'status' => 'pending',
        ]);

        // Try to access resubmission form
        $response = $this->actingAs($user)
            ->get(route('registrations.payment.resubmit', $registration));

        $response->assertRedirect(route('registrations.show', $registration))
                ->assertSessionHas('error');
    }

    /** @test */
    public function payment_history_shows_audit_trail()
    {
        // Create admin and user
        $admin = User::factory()->create(['role' => 'event_admin']);
        $user = User::factory()->create(['role' => 'member']);
        
        // Create registration
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'payment_required' => true,
            'payment_status' => 'verified',
        ]);

        // Create audit log entries
        \DB::table('registration_audit_logs')->insert([
            'registration_id' => $registration->id,
            'admin_user_id' => $admin->id,
            'action' => 'payment_approved',
            'notes' => 'Payment verified successfully',
            'created_at' => now(),
        ]);

        // User views payment history
        $response = $this->actingAs($user)
            ->get(route('registrations.payment.history', $registration));

        $response->assertStatus(200)
                ->assertSee('Payment approved')
                ->assertSee($admin->name)
                ->assertSee('Payment verified successfully');
    }
}