<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\Fest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompleteRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_for_individual_event_without_payment()
    {
        $user = $this->createMember();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 0,
            'max_participants' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_type' => 'individual',
            'individual_name' => 'John Doe',
            'payment_required' => false,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_can_register_for_individual_event_with_payment()
    {
        $user = $this->createMember();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 100,
            'max_participants' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe',
                'payment_method' => 'bkash',
                'transaction_id' => 'TXN123456',
                'payment_date' => now()->toDateString()
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_type' => 'individual',
            'payment_required' => true,
            'payment_amount' => 100,
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_status' => 'pending',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_can_create_team_registration()
    {
        $leader = $this->createMember();
        $member1 = $this->createMember();
        $member2 = $this->createMember();
        
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'team',
            'fee_amount' => 0,
            'max_participants' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        $response = $this->actingAs($leader)
            ->post(route('registrations.team', $event), [
                'team_name' => 'Awesome Team',
                'team_members' => [$member1->id, $member2->id]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $leader->id)
            ->first();

        $this->assertNotNull($registration);
        $this->assertEquals('team', $registration->registration_type);
        $this->assertEquals('Awesome Team', $registration->team_name);
        $this->assertCount(2, $registration->team_members_json);
    }

    /** @test */
    public function admin_can_approve_registration_without_payment()
    {
        $admin = $this->createEventAdmin();
        $registration = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => false
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.registrations.approve', $registration));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
    }

    /** @test */
    public function admin_can_verify_payment_and_approve_registration()
    {
        $admin = $this->createEventAdmin();
        $registration = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => true,
            'payment_status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456'
        ]);

        // First verify payment
        $response = $this->actingAs($admin)
            ->patch(route('admin.registrations.verify-payment', $registration), [
                'action' => 'approve',
                'admin_notes' => 'Payment verified successfully'
            ]);

        $response->assertRedirect();
        $registration->refresh();
        $this->assertEquals('verified', $registration->payment_status);

        // Then approve registration
        $response = $this->actingAs($admin)
            ->patch(route('admin.registrations.approve', $registration));

        $response->assertRedirect();
        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
    }

    /** @test */
    public function admin_can_reject_payment_with_reason()
    {
        $admin = $this->createEventAdmin();
        $registration = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => true,
            'payment_status' => 'pending'
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.registrations.verify-payment', $registration), [
                'action' => 'reject',
                'admin_notes' => 'Invalid transaction ID'
            ]);

        $response->assertRedirect();
        $registration->refresh();
        
        $this->assertEquals('rejected', $registration->payment_status);
        $this->assertEquals('Invalid transaction ID', $registration->admin_notes);
    }

    /** @test */
    public function user_can_resubmit_payment_after_rejection()
    {
        $user = $this->createMember();
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_required' => true,
            'payment_status' => 'rejected'
        ]);

        $response = $this->actingAs($user)
            ->patch(route('registrations.resubmit-payment', $registration), [
                'payment_method' => 'nagad',
                'transaction_id' => 'NEW_TXN789',
                'payment_date' => now()->toDateString()
            ]);

        $response->assertRedirect();
        $registration->refresh();
        
        $this->assertEquals('pending', $registration->payment_status);
        $this->assertEquals('nagad', $registration->payment_method);
        $this->assertEquals('NEW_TXN789', $registration->transaction_id);
    }

    /** @test */
    public function registration_is_blocked_when_event_is_full()
    {
        $user = $this->createMember();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'max_participants' => 2
        ]);

        // Fill up the event
        Registration::factory()->count(2)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function registration_is_blocked_after_deadline()
    {
        $user = $this->createMember();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'registration_deadline' => now()->subDay()
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function user_cannot_register_twice_for_same_event()
    {
        $user = $this->createMember();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual'
        ]);

        // First registration
        Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        // Attempt second registration
        $response = $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertEquals(1, Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)->count());
    }

    /** @test */
    public function complete_workflow_individual_registration_with_payment()
    {
        // Setup
        $user = $this->createMember();
        $admin = $this->createEventAdmin();
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'individual',
            'fee_amount' => 150,
            'max_participants' => 50,
            'registration_deadline' => now()->addWeek()
        ]);

        // Step 1: User registers for event
        $this->actingAs($user)
            ->post(route('registrations.individual', $event), [
                'individual_name' => 'John Doe',
                'payment_method' => 'bkash',
                'transaction_id' => 'TXN123456',
                'payment_date' => now()->toDateString()
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($registration);
        $this->assertEquals('pending', $registration->status);
        $this->assertEquals('pending', $registration->payment_status);

        // Step 2: Admin verifies payment
        $this->actingAs($admin)
            ->patch(route('admin.registrations.verify-payment', $registration), [
                'action' => 'approve',
                'admin_notes' => 'Payment verified successfully'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('verified', $registration->payment_status);

        // Step 3: Admin approves registration
        $this->actingAs($admin)
            ->patch(route('admin.registrations.approve', $registration))
            ->assertRedirect()
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('approved', $registration->status);

        // Step 4: User can view their registration
        $this->actingAs($user)
            ->get(route('registrations.history'))
            ->assertOk()
            ->assertSee($event->title)
            ->assertSee('Approved');
    }

    /** @test */
    public function complete_workflow_team_registration_with_payment()
    {
        // Setup
        $leader = $this->createMember();
        $member1 = $this->createMember();
        $member2 = $this->createMember();
        $admin = $this->createEventAdmin();
        
        $event = Event::factory()->create([
            'status' => 'published',
            'registration_type' => 'team',
            'fee_amount' => 300,
            'max_participants' => 20,
            'registration_deadline' => now()->addWeek()
        ]);

        // Step 1: Team leader creates team registration
        $this->actingAs($leader)
            ->post(route('registrations.team', $event), [
                'team_name' => 'Dream Team',
                'team_members' => [$member1->id, $member2->id],
                'payment_method' => 'nagad',
                'transaction_id' => 'TEAM_TXN789',
                'payment_date' => now()->toDateString()
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $leader->id)
            ->first();

        $this->assertNotNull($registration);
        $this->assertEquals('team', $registration->registration_type);
        $this->assertEquals('Dream Team', $registration->team_name);
        $this->assertCount(2, $registration->team_members_json);

        // Step 2: Admin verifies payment and approves
        $this->actingAs($admin)
            ->patch(route('admin.registrations.verify-payment', $registration), [
                'action' => 'approve',
                'admin_notes' => 'Team payment verified'
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.registrations.approve', $registration))
            ->assertRedirect();

        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
        $this->assertEquals('verified', $registration->payment_status);

        // Step 3: All team members can view the registration
        foreach ([$leader, $member1, $member2] as $member) {
            $this->actingAs($member)
                ->get(route('registrations.history'))
                ->assertOk()
                ->assertSee($event->title);
        }
    }

    /** @test */
    public function admin_can_export_registration_data()
    {
        $admin = $this->createEventAdmin();
        $event = Event::factory()->create();
        
        Registration::factory()->count(5)->create([
            'event_id' => $event->id,
            'status' => 'approved'
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.registrations.export', $event));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $event->title . '_registrations.csv"');
    }

    /** @test */
    public function member_cannot_access_admin_registration_management()
    {
        $member = $this->createMember();
        $registration = Registration::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.registrations.index'))
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.registrations.approve', $registration))
            ->assertForbidden();
    }

    /** @test */
    public function content_admin_cannot_manage_registrations()
    {
        $contentAdmin = $this->createContentAdmin();
        $registration = Registration::factory()->create();

        $this->actingAs($contentAdmin)
            ->get(route('admin.registrations.index'))
            ->assertForbidden();

        $this->actingAs($contentAdmin)
            ->patch(route('admin.registrations.approve', $registration))
            ->assertForbidden();
    }
}