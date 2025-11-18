<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use App\Models\Registration;
use App\Services\RegistrationService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $registrationService;
    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registrationService = app(RegistrationService::class);
        $this->notificationService = app(NotificationService::class);
    }

    /** @test */
    public function user_can_view_team_registration_form()
    {
        $user = User::factory()->create();
        $fest = Fest::factory()->create();
        $event = Event::factory()->create([
            'fest_id' => $fest->id,
            'registration_type' => 'team',
            'max_participants' => 20,
            'fee_amount' => 100,
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->get(route('registrations.team', $event));

        $response->assertStatus(200);
        $response->assertViewIs('registrations.team');
        $response->assertViewHas('event', $event);
        $response->assertSee('Team Registration');
        $response->assertSee($event->title);
    }

    /** @test */
    public function user_cannot_access_team_registration_for_individual_only_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
        ]);

        $response = $this->actingAs($user)
            ->get(route('registrations.team', $event));

        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('error', 'This event does not allow team registration.');
    }

    /** @test */
    public function user_can_register_team_successfully()
    {
        $teamLeader = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'max_participants' => 20,
            'fee_amount' => 100,
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => [$member1->id, $member2->id],
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($teamLeader)
            ->post(route('registrations.team.store', $event), $teamData);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'status' => 'pending',
        ]);

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $teamLeader->id)
            ->first();

        $response->assertRedirect(route('registrations.show', $registration));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function team_registration_validates_required_fields()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.team.store', $event), []);

        $response->assertSessionHasErrors(['team_name', 'team_members']);
    }

    /** @test */
    public function team_registration_validates_payment_fields_when_required()
    {
        $user = User::factory()->create();
        $member = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'fee_amount' => 100,
        ]);

        $response = $this->actingAs($user)
            ->post(route('registrations.team.store', $event), [
                'team_name' => 'Test Team',
                'team_members' => [$member->id],
            ]);

        $response->assertSessionHasErrors(['payment_method', 'transaction_id', 'payment_date']);
    }

    /** @test */
    public function user_can_manage_team_registration()
    {
        $teamLeader = User::factory()->create();
        $member = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($teamLeader)
            ->get(route('registrations.team.manage', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('registrations.manage-team');
        $response->assertSee('Manage Team');
        $response->assertSee('Test Team');
    }

    /** @test */
    public function user_cannot_manage_other_users_team_registration()
    {
        $teamLeader = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('registrations.team.manage', $registration));

        $response->assertStatus(403);
    }

    /** @test */
    public function team_leader_can_add_team_member()
    {
        $teamLeader = User::factory()->create();
        $newMember = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'status' => 'published',
            'event_date' => now()->addDays(7),
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
            'team_members_json' => [],
        ]);

        $response = $this->actingAs($teamLeader)
            ->post(route('registrations.team.add-member', $registration), [
                'user_id' => $newMember->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $teamMembers = $registration->team_members_json;
        
        $this->assertCount(1, $teamMembers);
        $this->assertEquals($newMember->id, $teamMembers[0]['user_id']);
        $this->assertEquals('pending_invitation', $teamMembers[0]['status']);
    }

    /** @test */
    public function team_leader_can_remove_team_member()
    {
        $teamLeader = User::factory()->create();
        $member = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
            'team_members_json' => [
                [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'status' => 'pending_invitation',
                ]
            ],
        ]);

        $response = $this->actingAs($teamLeader)
            ->post(route('registrations.team.remove-member', $registration), [
                'user_id' => $member->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEmpty($registration->team_members_json);
    }

    /** @test */
    public function user_can_accept_team_invitation()
    {
        $teamLeader = User::factory()->create();
        $invitedMember = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
            'team_members_json' => [
                [
                    'user_id' => $invitedMember->id,
                    'name' => $invitedMember->name,
                    'email' => $invitedMember->email,
                    'status' => 'pending_invitation',
                ]
            ],
        ]);

        $response = $this->actingAs($invitedMember)
            ->post(route('registrations.accept-invitation', $registration));

        $response->assertRedirect(route('registrations.show', $registration));
        $response->assertSessionHas('success');

        $registration->refresh();
        $teamMembers = $registration->team_members_json;
        $this->assertEquals('accepted', $teamMembers[0]['status']);
    }

    /** @test */
    public function user_can_decline_team_invitation()
    {
        $teamLeader = User::factory()->create();
        $invitedMember = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        $registration = Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $teamLeader->id,
            'registration_type' => 'team',
            'team_name' => 'Test Team',
            'status' => 'pending',
            'team_members_json' => [
                [
                    'user_id' => $invitedMember->id,
                    'name' => $invitedMember->name,
                    'email' => $invitedMember->email,
                    'status' => 'pending_invitation',
                ]
            ],
        ]);

        $response = $this->actingAs($invitedMember)
            ->post(route('registrations.decline-invitation', $registration));

        $response->assertRedirect(route('events.show', $registration->event));
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEmpty($registration->team_members_json);
    }

    /** @test */
    public function team_registration_prevents_duplicate_members()
    {
        $teamLeader = User::factory()->create();
        $member = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
        ]);

        // First registration with the member
        Registration::factory()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'registration_type' => 'individual',
            'status' => 'pending',
        ]);

        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => [$member->id],
        ];

        $response = $this->actingAs($teamLeader)
            ->post(route('registrations.team.store', $event), $teamData);

        $response->assertSessionHasErrors(['registration']);
    }

    /** @test */
    public function team_registration_calculates_correct_payment_amount()
    {
        $teamLeader = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        
        $event = Event::factory()->create([
            'registration_type' => 'team',
            'fee_amount' => 50,
        ]);

        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => [$member1->id, $member2->id],
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->format('Y-m-d'),
        ];

        $this->actingAs($teamLeader)
            ->post(route('registrations.team.store', $event), $teamData);

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $teamLeader->id)
            ->first();

        // Team size: 3 (leader + 2 members) * 50 = 150
        $this->assertEquals(150, $registration->payment_amount);
    }
}