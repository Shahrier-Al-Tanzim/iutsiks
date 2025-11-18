<?php

namespace Tests\Unit;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class RegistrationModelTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $registration = new Registration();
        $expected = [
            'event_id', 'user_id', 'registration_type', 'team_name',
            'team_members_json', 'individual_name', 'payment_required',
            'payment_amount', 'payment_status', 'payment_method',
            'transaction_id', 'payment_date', 'admin_notes', 'status', 'registered_at'
        ];
        
        $this->assertEquals($expected, $registration->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $registration = Registration::factory()->create([
            'team_members_json' => ['user1', 'user2'],
            'payment_required' => true,
            'payment_amount' => 100.50,
            'payment_date' => '2024-12-25',
        ]);
        
        $this->assertIsArray($registration->team_members_json);
        $this->assertIsBool($registration->payment_required);
        $this->assertEquals('100.50', $registration->payment_amount);
        $this->assertInstanceOf(\Carbon\Carbon::class, $registration->payment_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $registration->registered_at);
    }

    /** @test */
    public function it_belongs_to_event()
    {
        $event = Event::factory()->create();
        $registration = Registration::factory()->create(['event_id' => $event->id]);
        
        $this->assertInstanceOf(Event::class, $registration->event);
        $this->assertEquals($event->id, $registration->event->id);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $registration = Registration::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $registration->user);
        $this->assertEquals($user->id, $registration->user->id);
    }

    /** @test */
    public function it_gets_team_members_for_team_registration()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $registration = Registration::factory()->create([
            'registration_type' => 'team',
            'team_members_json' => [
                ['user_id' => $user1->id, 'name' => $user1->name],
                ['user_id' => $user2->id, 'name' => $user2->name]
            ]
        ]);
        
        $teamMembers = $registration->team_members;
        
        $this->assertCount(2, $teamMembers);
        $this->assertTrue($teamMembers->contains('id', $user1->id));
        $this->assertTrue($teamMembers->contains('id', $user2->id));
    }

    /** @test */
    public function it_returns_empty_collection_for_individual_registration()
    {
        $registration = Registration::factory()->create([
            'registration_type' => 'individual',
            'team_members_json' => null
        ]);
        
        $teamMembers = $registration->team_members;
        
        $this->assertCount(0, $teamMembers);
    }

    /** @test */
    public function it_sets_team_members_from_user_models()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $registration = Registration::factory()->create(['registration_type' => 'team']);
        
        $registration->setTeamMembers(collect([$user1, $user2]));
        $registration->save();
        
        $this->assertCount(2, $registration->team_members_json);
        $this->assertEquals($user1->id, $registration->team_members_json[0]['user_id']);
        $this->assertEquals($user2->id, $registration->team_members_json[1]['user_id']);
    }

    /** @test */
    public function it_sets_team_members_from_array()
    {
        $registration = Registration::factory()->create(['registration_type' => 'team']);
        
        $memberData = [
            ['user_id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['user_id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com']
        ];
        
        $registration->setTeamMembers($memberData);
        
        $this->assertCount(2, $registration->team_members_json);
        $this->assertEquals('John Doe', $registration->team_members_json[0]['name']);
        $this->assertEquals('Jane Doe', $registration->team_members_json[1]['name']);
    }

    /** @test */
    public function it_gets_team_member_count()
    {
        $teamRegistration = Registration::factory()->create([
            'registration_type' => 'team',
            'team_members_json' => [
                ['user_id' => 1, 'name' => 'User 1'],
                ['user_id' => 2, 'name' => 'User 2'],
                ['user_id' => 3, 'name' => 'User 3']
            ]
        ]);
        
        $individualRegistration = Registration::factory()->create([
            'registration_type' => 'individual'
        ]);
        
        $this->assertEquals(3, $teamRegistration->getTeamMemberCount());
        $this->assertEquals(0, $individualRegistration->getTeamMemberCount());
    }

    /** @test */
    public function it_checks_if_registration_is_approved()
    {
        $approvedRegistration = Registration::factory()->create(['status' => 'approved']);
        $pendingRegistration = Registration::factory()->create(['status' => 'pending']);
        
        $this->assertTrue($approvedRegistration->isApproved());
        $this->assertFalse($pendingRegistration->isApproved());
    }

    /** @test */
    public function it_checks_if_registration_is_pending()
    {
        $pendingRegistration = Registration::factory()->create(['status' => 'pending']);
        $approvedRegistration = Registration::factory()->create(['status' => 'approved']);
        
        $this->assertTrue($pendingRegistration->isPending());
        $this->assertFalse($approvedRegistration->isPending());
    }

    /** @test */
    public function it_checks_if_registration_is_rejected()
    {
        $rejectedRegistration = Registration::factory()->create(['status' => 'rejected']);
        $approvedRegistration = Registration::factory()->create(['status' => 'approved']);
        
        $this->assertTrue($rejectedRegistration->isRejected());
        $this->assertFalse($approvedRegistration->isRejected());
    }

    /** @test */
    public function it_checks_if_registration_is_cancelled()
    {
        $cancelledRegistration = Registration::factory()->create(['status' => 'cancelled']);
        $approvedRegistration = Registration::factory()->create(['status' => 'approved']);
        
        $this->assertTrue($cancelledRegistration->isCancelled());
        $this->assertFalse($approvedRegistration->isCancelled());
    }

    /** @test */
    public function it_checks_if_payment_is_verified()
    {
        $verifiedRegistration = Registration::factory()->create(['payment_status' => 'verified']);
        $pendingRegistration = Registration::factory()->create(['payment_status' => 'pending']);
        
        $this->assertTrue($verifiedRegistration->isPaymentVerified());
        $this->assertFalse($pendingRegistration->isPaymentVerified());
    }

    /** @test */
    public function it_checks_if_payment_is_pending()
    {
        $pendingRegistration = Registration::factory()->create(['payment_status' => 'pending']);
        $verifiedRegistration = Registration::factory()->create(['payment_status' => 'verified']);
        
        $this->assertTrue($pendingRegistration->isPaymentPending());
        $this->assertFalse($verifiedRegistration->isPaymentPending());
    }

    /** @test */
    public function it_checks_if_payment_is_rejected()
    {
        $rejectedRegistration = Registration::factory()->create(['payment_status' => 'rejected']);
        $verifiedRegistration = Registration::factory()->create(['payment_status' => 'verified']);
        
        $this->assertTrue($rejectedRegistration->isPaymentRejected());
        $this->assertFalse($verifiedRegistration->isPaymentRejected());
    }

    /** @test */
    public function it_checks_if_needs_payment_verification()
    {
        $needsVerification = Registration::factory()->create([
            'payment_required' => true,
            'payment_status' => 'pending'
        ]);
        
        $noPaymentRequired = Registration::factory()->create([
            'payment_required' => false,
            'payment_status' => 'pending'
        ]);
        
        $alreadyVerified = Registration::factory()->create([
            'payment_required' => true,
            'payment_status' => 'verified'
        ]);
        
        $this->assertTrue($needsVerification->needsPaymentVerification());
        $this->assertFalse($noPaymentRequired->needsPaymentVerification());
        $this->assertFalse($alreadyVerified->needsPaymentVerification());
    }

    /** @test */
    public function it_checks_if_can_be_approved()
    {
        $canBeApproved = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => true,
            'payment_status' => 'verified'
        ]);
        
        $alreadyApproved = Registration::factory()->create([
            'status' => 'approved',
            'payment_required' => true,
            'payment_status' => 'verified'
        ]);
        
        $paymentNotVerified = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => true,
            'payment_status' => 'pending'
        ]);
        
        $noPaymentRequired = Registration::factory()->create([
            'status' => 'pending',
            'payment_required' => false
        ]);
        
        $this->assertTrue($canBeApproved->canBeApproved());
        $this->assertFalse($alreadyApproved->canBeApproved());
        $this->assertFalse($paymentNotVerified->canBeApproved());
        $this->assertTrue($noPaymentRequired->canBeApproved());
    }

    /** @test */
    public function it_gets_participant_name_for_individual_registration()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $registration = Registration::factory()->create([
            'user_id' => $user->id,
            'registration_type' => 'individual',
            'individual_name' => 'Custom Name'
        ]);
        
        $this->assertEquals('Custom Name', $registration->getParticipantName());
    }

    /** @test */
    public function it_gets_participant_name_for_team_registration()
    {
        $registration = Registration::factory()->create([
            'registration_type' => 'team',
            'team_name' => 'Awesome Team'
        ]);
        
        $this->assertEquals('Awesome Team', $registration->getParticipantName());
    }

    /** @test */
    public function it_gets_payment_status_badge_class()
    {
        $verified = Registration::factory()->create(['payment_status' => 'verified']);
        $rejected = Registration::factory()->create(['payment_status' => 'rejected']);
        $pending = Registration::factory()->create(['payment_status' => 'pending']);
        
        $this->assertEquals('bg-green-100 text-green-800', $verified->getPaymentStatusBadgeClass());
        $this->assertEquals('bg-red-100 text-red-800', $rejected->getPaymentStatusBadgeClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', $pending->getPaymentStatusBadgeClass());
    }

    /** @test */
    public function it_gets_status_badge_class()
    {
        $approved = Registration::factory()->create(['status' => 'approved']);
        $rejected = Registration::factory()->create(['status' => 'rejected']);
        $cancelled = Registration::factory()->create(['status' => 'cancelled']);
        $pending = Registration::factory()->create(['status' => 'pending']);
        
        $this->assertEquals('bg-green-100 text-green-800', $approved->getStatusBadgeClass());
        $this->assertEquals('bg-red-100 text-red-800', $rejected->getStatusBadgeClass());
        $this->assertEquals('bg-gray-100 text-gray-800', $cancelled->getStatusBadgeClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', $pending->getStatusBadgeClass());
    }

    /** @test */
    public function it_scopes_approved_registrations()
    {
        Registration::factory()->create(['status' => 'approved']);
        Registration::factory()->create(['status' => 'pending']);
        Registration::factory()->create(['status' => 'approved']);
        
        $approved = Registration::approved()->get();
        
        $this->assertCount(2, $approved);
        $approved->each(function ($registration) {
            $this->assertEquals('approved', $registration->status);
        });
    }

    /** @test */
    public function it_scopes_pending_registrations()
    {
        Registration::factory()->create(['status' => 'approved']);
        Registration::factory()->create(['status' => 'pending']);
        Registration::factory()->create(['status' => 'pending']);
        
        $pending = Registration::pending()->get();
        
        $this->assertCount(2, $pending);
        $pending->each(function ($registration) {
            $this->assertEquals('pending', $registration->status);
        });
    }

    /** @test */
    public function it_scopes_payment_verified_registrations()
    {
        Registration::factory()->create(['payment_status' => 'verified']);
        Registration::factory()->create(['payment_status' => 'pending']);
        Registration::factory()->create(['payment_status' => 'verified']);
        
        $verified = Registration::paymentVerified()->get();
        
        $this->assertCount(2, $verified);
        $verified->each(function ($registration) {
            $this->assertEquals('verified', $registration->payment_status);
        });
    }

    /** @test */
    public function it_scopes_registrations_needing_payment_verification()
    {
        Registration::factory()->create([
            'payment_required' => true,
            'payment_status' => 'pending'
        ]);
        Registration::factory()->create([
            'payment_required' => false,
            'payment_status' => 'pending'
        ]);
        Registration::factory()->create([
            'payment_required' => true,
            'payment_status' => 'verified'
        ]);
        
        $needsVerification = Registration::needsPaymentVerification()->get();
        
        $this->assertCount(1, $needsVerification);
    }

    /** @test */
    public function it_scopes_team_registrations()
    {
        Registration::factory()->create(['registration_type' => 'team']);
        Registration::factory()->create(['registration_type' => 'individual']);
        Registration::factory()->create(['registration_type' => 'team']);
        
        $teamRegistrations = Registration::team()->get();
        
        $this->assertCount(2, $teamRegistrations);
        $teamRegistrations->each(function ($registration) {
            $this->assertEquals('team', $registration->registration_type);
        });
    }

    /** @test */
    public function it_scopes_individual_registrations()
    {
        Registration::factory()->create(['registration_type' => 'team']);
        Registration::factory()->create(['registration_type' => 'individual']);
        Registration::factory()->create(['registration_type' => 'individual']);
        
        $individualRegistrations = Registration::individual()->get();
        
        $this->assertCount(2, $individualRegistrations);
        $individualRegistrations->each(function ($registration) {
            $this->assertEquals('individual', $registration->registration_type);
        });
    }
}