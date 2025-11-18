<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmation(Registration $registration): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'event_date' => $event->event_date->format('F j, Y'),
                'event_time' => $event->event_time ? $event->event_time->format('g:i A') : 'TBA',
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
                'payment_required' => $registration->payment_required,
                'payment_amount' => $registration->payment_amount,
                'registration_id' => $registration->id,
                'status' => $registration->status,
            ];
            
            // Send email using Laravel's Mail facade
            Mail::send('emails.registration-confirmation', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Registration Confirmation - ' . $event->title);
            });
            
            // Log successful notification
            Log::info('Registration confirmation sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'event_id' => $event->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send registration confirmation', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send payment status update notification
     */
    public function sendPaymentStatusUpdate(Registration $registration, string $status, string $reason = null): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'registration_id' => $registration->id,
                'payment_status' => $status,
                'payment_amount' => $registration->payment_amount,
                'transaction_id' => $registration->transaction_id,
                'reason' => $reason,
                'is_approved' => $status === 'verified',
                'is_rejected' => $status === 'rejected',
            ];
            
            $subject = $status === 'verified' 
                ? 'Payment Approved - ' . $event->title
                : 'Payment Update - ' . $event->title;
            
            Mail::send('emails.payment-status-update', $data, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                        ->subject($subject);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'payment-status-update', $data);
            }
            
            Log::info('Payment status notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'status' => $status,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment status notification', [
                'registration_id' => $registration->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send registration approval notification
     */
    public function sendRegistrationApproval(Registration $registration): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'event_date' => $event->event_date->format('F j, Y'),
                'event_time' => $event->event_time ? $event->event_time->format('g:i A') : 'TBA',
                'event_location' => $event->location,
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
                'registration_id' => $registration->id,
            ];
            
            Mail::send('emails.registration-approved', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Registration Approved - ' . $event->title);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'registration-approved', $data);
            }
            
            Log::info('Registration approval notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send registration approval notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send registration rejection notification
     */
    public function sendRegistrationRejection(Registration $registration, string $reason): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'registration_id' => $registration->id,
                'reason' => $reason,
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
            ];
            
            Mail::send('emails.registration-rejected', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Registration Update - ' . $event->title);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'registration-rejected', $data);
            }
            
            Log::info('Registration rejection notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send registration rejection notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send registration cancellation notification
     */
    public function sendRegistrationCancellation(Registration $registration): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'event_date' => $event->event_date->format('F j, Y'),
                'registration_id' => $registration->id,
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
                'cancelled_at' => now()->format('F j, Y g:i A'),
            ];
            
            Mail::send('emails.registration-cancelled', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Registration Cancelled - ' . $event->title);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'registration-cancelled', $data);
            }
            
            Log::info('Registration cancellation notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send registration cancellation notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send event reminder notification
     */
    public function sendEventReminder(Event $event, int $daysBefore = 1): int
    {
        $sentCount = 0;
        
        try {
            $registrations = $event->registrations()
                ->where('status', 'approved')
                ->with('user')
                ->get();
            
            foreach ($registrations as $registration) {
                $user = $registration->user;
                
                $data = [
                    'user_name' => $user->name,
                    'event_title' => $event->title,
                    'event_date' => $event->event_date->format('F j, Y'),
                    'event_time' => $event->event_time ? $event->event_time->format('g:i A') : 'TBA',
                    'event_location' => $event->location,
                    'days_before' => $daysBefore,
                    'registration_type' => $registration->registration_type,
                    'team_name' => $registration->team_name,
                ];
                
                try {
                    Mail::send('emails.event-reminder', $data, function ($message) use ($user, $event, $daysBefore) {
                        $message->to($user->email, $user->name)
                                ->subject("Reminder: {$event->title} in {$daysBefore} day(s)");
                    });
                    
                    $sentCount++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to send event reminder to user', [
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Event reminders sent', [
                'event_id' => $event->id,
                'sent_count' => $sentCount,
                'days_before' => $daysBefore,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send event reminders', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $sentCount;
    }
    
    /**
     * Send team invitation notification
     */
    public function sendTeamInvitation(Registration $registration, User $invitee): bool
    {
        try {
            $teamLeader = $registration->user;
            $event = $registration->event;
            
            $data = [
                'invitee_name' => $invitee->name,
                'team_leader_name' => $teamLeader->name,
                'team_name' => $registration->team_name,
                'event_title' => $event->title,
                'event_date' => $event->event_date->format('F j, Y'),
                'event_time' => $event->event_time ? $event->event_time->format('g:i A') : 'TBA',
                'event_location' => $event->location,
                'registration_id' => $registration->id,
                'accept_url' => route('registrations.accept-invitation', $registration),
                'decline_url' => route('registrations.decline-invitation', $registration),
            ];
            
            Mail::send('emails.team-invitation', $data, function ($message) use ($invitee, $event, $registration) {
                $message->to($invitee->email, $invitee->name)
                        ->subject("Team Invitation: {$registration->team_name} - {$event->title}");
            });
            
            Log::info('Team invitation sent', [
                'invitee_id' => $invitee->id,
                'team_leader_id' => $teamLeader->id,
                'event_id' => $event->id,
                'registration_id' => $registration->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send team invitation', [
                'invitee_id' => $invitee->id,
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send payment approval notification
     */
    public function sendPaymentApprovalNotification(Registration $registration): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'registration_id' => $registration->id,
                'payment_amount' => $registration->payment_amount,
                'transaction_id' => $registration->transaction_id,
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
                'event_date' => $event->event_date->format('F j, Y'),
                'event_time' => $event->event_time ? $event->event_time->format('g:i A') : 'TBA',
            ];
            
            Mail::send('emails.payment-approved', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Payment Approved - ' . $event->title);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'payment-approved', $data);
            }
            
            Log::info('Payment approval notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment approval notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send payment rejection notification
     */
    public function sendPaymentRejectionNotification(Registration $registration, string $reason): bool
    {
        try {
            $user = $registration->user;
            $event = $registration->event;
            
            $data = [
                'user_name' => $user->name,
                'event_title' => $event->title,
                'registration_id' => $registration->id,
                'payment_amount' => $registration->payment_amount,
                'transaction_id' => $registration->transaction_id,
                'rejection_reason' => $reason,
                'registration_type' => $registration->registration_type,
                'team_name' => $registration->team_name,
                'resubmit_url' => route('registrations.payment.resubmit', $registration),
            ];
            
            Mail::send('emails.payment-rejected', $data, function ($message) use ($user, $event) {
                $message->to($user->email, $user->name)
                        ->subject('Payment Verification Required - ' . $event->title);
            });
            
            // Send to team members if it's a team registration
            if ($registration->registration_type === 'team' && $registration->team_members_json) {
                $this->notifyTeamMembers($registration, 'payment-rejected', $data);
            }
            
            Log::info('Payment rejection notification sent', [
                'registration_id' => $registration->id,
                'user_id' => $user->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment rejection notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Send registration approval notification
     */
    public function sendRegistrationApprovalNotification(Registration $registration): bool
    {
        return $this->sendRegistrationApproval($registration);
    }
    
    /**
     * Send registration rejection notification
     */
    public function sendRegistrationRejectionNotification(Registration $registration, string $reason): bool
    {
        return $this->sendRegistrationRejection($registration, $reason);
    }

    /**
     * Send bulk notification to multiple users
     */
    public function sendBulkNotification(array $userIds, string $subject, string $template, array $data): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        
        $users = User::whereIn('id', $userIds)->get();
        
        foreach ($users as $user) {
            try {
                $personalizedData = array_merge($data, ['user_name' => $user->name]);
                
                Mail::send($template, $personalizedData, function ($message) use ($user, $subject) {
                    $message->to($user->email, $user->name)
                            ->subject($subject);
                });
                
                $results['success']++;
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to send to {$user->email}: " . $e->getMessage();
                
                Log::error('Failed to send bulk notification to user', [
                    'user_id' => $user->id,
                    'template' => $template,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * Notify team members
     */
    private function notifyTeamMembers(Registration $registration, string $template, array $data): void
    {
        if (!$registration->team_members_json) {
            return;
        }
        
        $teamMemberIds = json_decode($registration->team_members_json, true);
        if (!is_array($teamMemberIds)) {
            return;
        }
        
        $teamMembers = User::whereIn('id', $teamMemberIds)->get();
        
        foreach ($teamMembers as $member) {
            try {
                $memberData = array_merge($data, [
                    'user_name' => $member->name,
                    'is_team_member' => true,
                    'team_leader_name' => $registration->user->name,
                ]);
                
                Mail::send("emails.{$template}", $memberData, function ($message) use ($member, $data) {
                    $message->to($member->email, $member->name)
                            ->subject($data['event_title'] . ' - Team Update');
                });
                
            } catch (\Exception $e) {
                Log::error('Failed to notify team member', [
                    'member_id' => $member->id,
                    'registration_id' => $registration->id,
                    'template' => $template,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}