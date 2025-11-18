<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RegistrationService;
use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\Fest;
use App\Exceptions\RegistrationException;
use App\Exceptions\EventCapacityException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class RegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationService $registrationService;
    private User $user;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->registrationService = new RegistrationService();
        
        // Create test user
        $this->user = User::factory()->create([
            'role' => 'member',
        ]);
        
        // Create test fest and event
        $fest = Fest::factory()->create();
        $this->event = Event::factory()->create([
            'fest_id' => $fest->id,
            'registration_type' => 'individual',
            'max_participants' => 10,
            'fee_amount' => 0,
            'registration_deadline' => now()->addDays(7),
        ]);
    }

    public function test_register_individual_success()
    {
        $data = [
            'individual_name' => 'John Doe',
        ];

        $registration = $this->registrationService->registerIndividual($this->event, $this->user, $data);

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertEquals($this->event->id, $registration->event_id);
        $this->assertEquals($this->user->id, $registration->user_id);
        $this->assertEquals('individual', $registration->registration_type);
        $this->assertEquals('John Doe', $registration->individual_name);
        $this->assertEquals('pending', $registration->status);
    }

    public function test_register_individual_with_payment()
    {
        $this->event->update(['fee_amount' => 100]);
        
        $data = [
            'individual_name' => 'John Doe',
            'payment_details' => [
                'payment_method' => 'bkash',
                'transaction_id' => 'TXN123456',
                'payment_date' => now()->format('Y-m-d'),
            ],
        ];

        $registration = $this->registrationService->registerIndividual($this->event, $this->user, $data);

        $this->assertTrue($registration->payment_required);
        $this->assertEquals(100, $registration->payment_amount);
        $this->assertEquals('bkash', $registration->payment_method);
        $this->assertEquals('TXN123456', $registration->transaction_id);
        $this->assertEquals('pending', $registration->payment_status);
    }

    public function test_register_individual_fails_when_already_registered()
    {
        // Create existing registration
        Registration::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessage('User is already registered for this event');

        $this->registrationService->registerIndividual($this->event, $this->user, []);
    }

    public function test_register_individual_fails_when_deadline_passed()
    {
        $this->event->update(['registration_deadline' => now()->subDays(1)]);

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessage('Registration deadline has passed');

        $this->registrationService->registerIndividual($this->event, $this->user, []);
    }

    public function test_register_individual_fails_when_capacity_full()
    {
        // Fill up the event
        for ($i = 0; $i < 10; $i++) {
            Registration::factory()->create([
                'event_id' => $this->event->id,
                'registration_type' => 'individual',
                'status' => 'approved',
            ]);
        }

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessage('Failed to register individual: Event has reached maximum capacity');

        $this->registrationService->registerIndividual($this->event, $this->user, []);
    }

    public function test_register_team_success()
    {
        $this->event->update(['registration_type' => 'team']);
        
        $teamMembers = User::factory()->count(3)->create();
        
        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => $teamMembers->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                ];
            })->toArray(),
        ];

        $registration = $this->registrationService->registerTeam($this->event, $this->user, $teamData);

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertEquals('team', $registration->registration_type);
        $this->assertEquals('Test Team', $registration->team_name);
        $this->assertNotNull($registration->team_members_json);
        
        $this->assertCount(3, $registration->team_members_json);
    }

    public function test_register_team_with_payment()
    {
        $this->event->update([
            'registration_type' => 'team',
            'fee_amount' => 50,
        ]);
        
        $teamMembers = User::factory()->count(2)->create();
        
        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => $teamMembers->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                ];
            })->toArray(),
            'payment_details' => [
                'payment_method' => 'nagad',
                'transaction_id' => 'TXN789012',
                'payment_date' => now()->format('Y-m-d'),
            ],
        ];

        $registration = $this->registrationService->registerTeam($this->event, $this->user, $teamData);

        // Payment amount should be fee_amount * team size (including leader)
        $this->assertEquals(150, $registration->payment_amount); // 50 * 3 members
        $this->assertEquals('nagad', $registration->payment_method);
    }

    public function test_register_team_fails_with_invalid_members()
    {
        $this->event->update(['registration_type' => 'team']);
        
        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => [999, 1000], // Non-existent user IDs
        ];

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessage('Some team members do not exist');

        $this->registrationService->registerTeam($this->event, $this->user, $teamData);
    }

    public function test_register_team_fails_when_member_already_registered()
    {
        $this->event->update(['registration_type' => 'team']);
        
        $teamMember = User::factory()->create();
        
        // Register the team member individually first
        Registration::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $teamMember->id,
            'status' => 'pending',
        ]);
        
        $teamData = [
            'team_name' => 'Test Team',
            'team_members' => [
                [
                    'user_id' => $teamMember->id,
                    'name' => $teamMember->name,
                    'email' => $teamMember->email,
                    'student_id' => $teamMember->student_id,
                ]
            ],
        ];

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessage('Some team members are already registered');

        $this->registrationService->registerTeam($this->event, $this->user, $teamData);
    }

    public function test_approve_registration()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'pending',
        ]);

        $result = $this->registrationService->approveRegistration($registration, $admin);

        $this->assertTrue($result);
        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
        $this->assertStringContainsString('Approved by', $registration->admin_notes);
    }

    public function test_reject_registration()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'pending',
        ]);

        $reason = 'Invalid payment details';
        $result = $this->registrationService->rejectRegistration($registration, $admin, $reason);

        $this->assertTrue($result);
        $registration->refresh();
        $this->assertEquals('rejected', $registration->status);
        $this->assertStringContainsString($reason, $registration->admin_notes);
    }

    public function test_check_availability()
    {
        // Create some registrations
        Registration::factory()->count(3)->create([
            'event_id' => $this->event->id,
            'registration_type' => 'individual',
            'status' => 'approved',
        ]);

        $availability = $this->registrationService->checkAvailability($this->event);

        $this->assertTrue($availability['available']);
        $this->assertEquals(3, $availability['total_registered']);
        $this->assertEquals(10, $availability['max_participants']);
        $this->assertEquals(7, $availability['remaining_spots']);
        $this->assertTrue($availability['registration_open']);
    }

    public function test_check_availability_with_team_registrations()
    {
        // Create team registration with 3 members + 1 leader = 4 total
        $teamMembers = User::factory()->count(3)->create();
        Registration::factory()->create([
            'event_id' => $this->event->id,
            'registration_type' => 'team',
            'team_members_json' => json_encode($teamMembers->pluck('id')->toArray()),
            'status' => 'approved',
        ]);

        $availability = $this->registrationService->checkAvailability($this->event);

        $this->assertEquals(4, $availability['total_registered']);
        $this->assertEquals(6, $availability['remaining_spots']);
    }

    public function test_export_registrations()
    {
        // Create approved registrations
        Registration::factory()->count(3)->create([
            'event_id' => $this->event->id,
            'status' => 'approved',
        ]);

        $filepath = $this->registrationService->exportRegistrations($this->event);

        $this->assertFileExists($filepath);
        $this->assertStringContainsString('registrations_', basename($filepath));
        $this->assertStringEndsWith('.csv', $filepath);

        // Clean up
        unlink($filepath);
    }

    public function test_process_payment()
    {
        $registration = Registration::factory()->create([
            'event_id' => $this->event->id,
            'payment_required' => true,
        ]);

        $paymentData = [
            'payment_method' => 'bkash',
            'transaction_id' => 'TXN123456',
            'payment_date' => now()->format('Y-m-d'),
        ];

        $result = $this->registrationService->processPayment($registration, $paymentData);

        $this->assertTrue($result);
        $registration->refresh();
        $this->assertEquals('bkash', $registration->payment_method);
        $this->assertEquals('TXN123456', $registration->transaction_id);
        $this->assertEquals('pending', $registration->payment_status);
    }
}