<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Exceptions\RegistrationException;
use App\Exceptions\EventCapacityException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegistrationService
{
    /**
     * Register an individual for an event
     */
    public function registerIndividual(Event $event, User $user, array $data): Registration
    {
        DB::beginTransaction();
        
        try {
            // Check if registration is allowed
            $this->validateRegistrationEligibility($event, $user);
            
            // Check capacity
            if (!$this->checkAvailability($event)['available']) {
                throw new EventCapacityException('Event has reached maximum capacity');
            }
            
            // Create registration
            $registration = new Registration([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'registration_type' => 'individual',
                'individual_name' => $data['individual_name'] ?? $user->name,
                'payment_required' => $event->fee_amount > 0,
                'payment_amount' => $event->fee_amount,
                'status' => 'pending',
                'registered_at' => now(),
            ]);
            
            // Add payment details if provided
            if ($event->fee_amount > 0 && isset($data['payment_details'])) {
                $registration->payment_method = $data['payment_details']['payment_method'];
                $registration->transaction_id = $data['payment_details']['transaction_id'];
                $registration->payment_date = $data['payment_details']['payment_date'];
                $registration->payment_status = 'pending';
            }
            
            $registration->save();
            
            DB::commit();
            
            return $registration;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RegistrationException('Failed to register individual: ' . $e->getMessage());
        }
    }
    
    /**
     * Register a team for an event
     */
    public function registerTeam(Event $event, User $leader, array $teamData): Registration
    {
        DB::beginTransaction();
        
        try {
            // Check if registration is allowed
            $this->validateRegistrationEligibility($event, $leader);
            
            // Validate team data
            $this->validateTeamData($teamData);
            
            // Check capacity for team size
            $teamSize = count($teamData['team_members']) + 1; // +1 for leader
            $availability = $this->checkAvailability($event);
            
            if ($availability['remaining_spots'] !== null && $availability['remaining_spots'] < $teamSize) {
                throw new EventCapacityException('Not enough spots remaining for team registration');
            }
            
            // Extract member IDs from team data
            $memberIds = collect($teamData['team_members'])->pluck('user_id')->toArray();
            
            // Verify all team members exist and are not already registered
            $this->validateTeamMembers($event, $memberIds, $leader);
            
            // Create registration
            $registration = new Registration([
                'event_id' => $event->id,
                'user_id' => $leader->id,
                'registration_type' => 'team',
                'team_name' => $teamData['team_name'],
                'team_members_json' => $teamData['team_members'],
                'payment_required' => $event->fee_amount > 0,
                'payment_amount' => $event->fee_amount * $teamSize,
                'status' => 'pending',
                'registered_at' => now(),
            ]);
            
            // Add payment details if provided
            if ($event->fee_amount > 0 && isset($teamData['payment_details'])) {
                $registration->payment_method = $teamData['payment_details']['payment_method'];
                $registration->transaction_id = $teamData['payment_details']['transaction_id'];
                $registration->payment_date = $teamData['payment_details']['payment_date'];
                $registration->payment_status = 'pending';
            }
            
            $registration->save();
            
            DB::commit();
            
            return $registration;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RegistrationException('Failed to register team: ' . $e->getMessage());
        }
    }
    
    /**
     * Process payment verification
     */
    public function processPayment(Registration $registration, array $paymentData): bool
    {
        try {
            $registration->update([
                'payment_method' => $paymentData['payment_method'],
                'transaction_id' => $paymentData['transaction_id'],
                'payment_date' => $paymentData['payment_date'],
                'payment_status' => 'pending',
            ]);
            
            return true;
        } catch (\Exception $e) {
            throw new RegistrationException('Failed to process payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Approve a registration
     */
    public function approveRegistration(Registration $registration, User $admin): bool
    {
        try {
            $registration->update([
                'status' => 'approved',
                'admin_notes' => 'Approved by ' . $admin->name . ' on ' . now()->format('Y-m-d H:i:s'),
            ]);
            
            if ($registration->payment_required) {
                $registration->update(['payment_status' => 'verified']);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new RegistrationException('Failed to approve registration: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a registration
     */
    public function rejectRegistration(Registration $registration, User $admin, string $reason): bool
    {
        try {
            $registration->update([
                'status' => 'rejected',
                'admin_notes' => 'Rejected by ' . $admin->name . ' on ' . now()->format('Y-m-d H:i:s') . '. Reason: ' . $reason,
            ]);
            
            if ($registration->payment_required) {
                $registration->update(['payment_status' => 'rejected']);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new RegistrationException('Failed to reject registration: ' . $e->getMessage());
        }
    }
    
    /**
     * Check event availability
     */
    public function checkAvailability(Event $event): array
    {
        $totalRegistered = $this->getTotalRegisteredCount($event);
        $maxParticipants = $event->max_participants;
        
        return [
            'available' => $maxParticipants === null || $totalRegistered < $maxParticipants,
            'total_registered' => $totalRegistered,
            'max_participants' => $maxParticipants,
            'remaining_spots' => $maxParticipants ? $maxParticipants - $totalRegistered : null,
            'registration_open' => $this->isRegistrationOpen($event),
        ];
    }
    
    /**
     * Export registrations for an event
     */
    public function exportRegistrations(Event $event): string
    {
        $registrations = $event->registrations()
            ->with('user')
            ->where('status', 'approved')
            ->get();
        
        $csvData = [];
        $csvData[] = ['Name', 'Email', 'Phone', 'Student ID', 'Registration Type', 'Team Name', 'Payment Status', 'Registered At'];
        
        foreach ($registrations as $registration) {
            $csvData[] = [
                $registration->registration_type === 'individual' 
                    ? $registration->individual_name 
                    : $registration->team_name,
                $registration->user->email,
                $registration->user->phone ?? 'N/A',
                $registration->user->student_id ?? 'N/A',
                ucfirst($registration->registration_type),
                $registration->team_name ?? 'N/A',
                ucfirst($registration->payment_status ?? 'N/A'),
                $registration->registered_at->format('Y-m-d H:i:s'),
            ];
        }
        
        $filename = 'registrations_' . $event->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Get total registered count including team members
     */
    private function getTotalRegisteredCount(Event $event): int
    {
        $registrations = $event->registrations()
            ->where('status', '!=', 'rejected')
            ->get();
        
        $total = 0;
        foreach ($registrations as $registration) {
            if ($registration->registration_type === 'team') {
                $teamMembers = json_decode($registration->team_members_json, true) ?? [];
                $total += count($teamMembers) + 1; // +1 for team leader
            } else {
                $total += 1;
            }
        }
        
        return $total;
    }
    
    /**
     * Check if registration is still open
     */
    private function isRegistrationOpen(Event $event): bool
    {
        if ($event->registration_deadline) {
            return now()->lt(Carbon::parse($event->registration_deadline));
        }
        
        return $event->event_date > now();
    }
    
    /**
     * Validate registration eligibility
     */
    private function validateRegistrationEligibility(Event $event, User $user): void
    {
        // Check if registration is open
        if (!$this->isRegistrationOpen($event)) {
            throw new RegistrationException('Registration deadline has passed');
        }
        
        // Check if user is already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->first();
            
        if ($existingRegistration) {
            throw new RegistrationException('User is already registered for this event');
        }
        
        // Check if event allows registration
        if ($event->registration_type === 'on_spot') {
            throw new RegistrationException('This event only accepts on-spot registration');
        }
    }
    
    /**
     * Validate team data
     */
    private function validateTeamData(array $teamData): void
    {
        if (empty($teamData['team_name'])) {
            throw new RegistrationException('Team name is required');
        }
        
        if (empty($teamData['team_members']) || !is_array($teamData['team_members'])) {
            throw new RegistrationException('Team members are required');
        }
        
        if (count($teamData['team_members']) < 1) {
            throw new RegistrationException('Team must have at least one member besides the leader');
        }
    }
    
    /**
     * Validate team members
     */
    private function validateTeamMembers(Event $event, array $memberIds, User $leader): void
    {
        // Check if all member IDs exist
        $existingUsers = User::whereIn('id', $memberIds)->pluck('id')->toArray();
        $missingUsers = array_diff($memberIds, $existingUsers);
        
        if (!empty($missingUsers)) {
            throw new RegistrationException('Some team members do not exist: ' . implode(', ', $missingUsers));
        }
        
        // Check if leader is not in team members
        if (in_array($leader->id, $memberIds)) {
            throw new RegistrationException('Team leader cannot be listed as a team member');
        }
        
        // Check if any team members are already registered for this event
        $allMemberIds = array_merge($memberIds, [$leader->id]);
        $existingRegistrations = Registration::where('event_id', $event->id)
            ->whereIn('user_id', $allMemberIds)
            ->where('status', '!=', 'rejected')
            ->with('user')
            ->get();
            
        if ($existingRegistrations->isNotEmpty()) {
            $registeredUsers = $existingRegistrations->pluck('user.name')->implode(', ');
            throw new RegistrationException('Some team members are already registered: ' . $registeredUsers);
        }
    }
}