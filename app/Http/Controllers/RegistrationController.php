<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use App\Services\NotificationService;
use App\Exceptions\RegistrationException;
use App\Exceptions\EventCapacityException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    protected $registrationService;
    protected $notificationService;

    public function __construct(RegistrationService $registrationService, NotificationService $notificationService)
    {
        $this->registrationService = $registrationService;
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Show individual registration form for an event
     */
    public function showIndividualForm(Event $event)
    {
        // Check if event allows individual registration
        if (!$event->allowsIndividualRegistration()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'This event does not allow individual registration.');
        }

        // Check if registration is open
        if (!$event->isRegistrationOpen()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Registration is closed for this event.');
        }

        // Check if user is already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingRegistration) {
            return redirect()->route('registrations.show', $existingRegistration)
                ->with('info', 'You are already registered for this event.');
        }

        // Get availability information
        $availability = $this->registrationService->checkAvailability($event);

        return view('registrations.individual', compact('event', 'availability'));
    }

    /**
     * Show team registration form for an event
     */
    public function showTeamForm(Event $event)
    {
        // Check if event allows team registration
        if (!$event->allowsTeamRegistration()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'This event does not allow team registration.');
        }

        // Check if registration is open
        if (!$event->isRegistrationOpen()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Registration is closed for this event.');
        }

        // Check if user is already registered
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingRegistration) {
            return redirect()->route('registrations.show', $existingRegistration)
                ->with('info', 'You are already registered for this event.');
        }

        // Get availability information
        $availability = $this->registrationService->checkAvailability($event);

        // Get available users for team selection (registered users only)
        $availableUsers = User::where('id', '!=', Auth::id())
            ->whereNotIn('id', function($query) use ($event) {
                $query->select('user_id')
                      ->from('registrations')
                      ->where('event_id', $event->id)
                      ->where('status', '!=', 'rejected');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_id']);

        return view('registrations.team', compact('event', 'availability', 'availableUsers'));
    }

    /**
     * Process individual registration
     */
    public function registerIndividual(Request $request, Event $event)
    {
        // Validate the request
        $validationRules = [
            'individual_name' => 'required|string|max:255',
        ];

        if ($event->requiresPayment()) {
            $validationRules['payment_method'] = 'required|in:bkash,nagad,bank_transfer,cash';
            $validationRules['transaction_id'] = 'required|string|max:255';
            $validationRules['payment_date'] = 'required|date|before_or_equal:today';
        }

        $validated = $request->validate($validationRules);

        try {
            $registrationData = [
                'individual_name' => $validated['individual_name'],
            ];

            // Add payment details if required
            if ($event->requiresPayment()) {
                $registrationData['payment_details'] = [
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => $validated['transaction_id'],
                    'payment_date' => $validated['payment_date'],
                ];
            }

            $registration = $this->registrationService->registerIndividual(
                $event,
                Auth::user(),
                $registrationData
            );

            // Send confirmation notification
            $this->notificationService->sendRegistrationConfirmation($registration);

            return redirect()->route('registrations.show', $registration)
                ->with('success', 'Registration submitted successfully! You will receive a confirmation email shortly.');

        } catch (RegistrationException $e) {
            return redirect()->back()
                ->withErrors(['registration' => $e->getMessage()])
                ->withInput();
        } catch (EventCapacityException $e) {
            return redirect()->route('events.show', $event)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process team registration
     */
    public function registerTeam(Request $request, Event $event)
    {
        // Validate the request
        $validationRules = [
            'team_name' => 'required|string|max:255',
            'team_members' => 'required|array|min:1|max:10',
            'team_members.*' => 'required|exists:users,id',
        ];

        if ($event->requiresPayment()) {
            $validationRules['payment_method'] = 'required|in:bkash,nagad,bank_transfer,cash';
            $validationRules['transaction_id'] = 'required|string|max:255';
            $validationRules['payment_date'] = 'required|date|before_or_equal:today';
        }

        $validated = $request->validate($validationRules);

        try {
            // Prepare team data
            $teamMembers = User::whereIn('id', $validated['team_members'])
                ->get(['id', 'name', 'email', 'student_id'])
                ->map(function ($user) {
                    return [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'student_id' => $user->student_id,
                        'role' => 'member',
                        'status' => 'pending_invitation'
                    ];
                })->toArray();

            $teamData = [
                'team_name' => $validated['team_name'],
                'team_members' => $teamMembers,
            ];

            // Add payment details if required
            if ($event->requiresPayment()) {
                $teamData['payment_details'] = [
                    'payment_method' => $validated['payment_method'],
                    'transaction_id' => $validated['transaction_id'],
                    'payment_date' => $validated['payment_date'],
                ];
            }

            $registration = $this->registrationService->registerTeam(
                $event,
                Auth::user(),
                $teamData
            );

            // Send team invitations
            $this->sendTeamInvitations($registration);

            // Send confirmation notification to team leader
            $this->notificationService->sendRegistrationConfirmation($registration);

            return redirect()->route('registrations.show', $registration)
                ->with('success', 'Team registration submitted successfully! Team invitations have been sent.');

        } catch (RegistrationException $e) {
            return redirect()->back()
                ->withErrors(['registration' => $e->getMessage()])
                ->withInput();
        } catch (EventCapacityException $e) {
            return redirect()->route('events.show', $event)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show registration details
     */
    public function show(Registration $registration)
    {
        // Ensure user can only view their own registrations
        if ($registration->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to registration.');
        }

        $registration->load(['event', 'user']);

        return view('registrations.show', compact('registration'));
    }

    /**
     * Show user's registration history
     */
    public function myRegistrations()
    {
        $registrations = Registration::where('user_id', Auth::id())
            ->with(['event', 'event.fest'])
            ->orderBy('registered_at', 'desc')
            ->paginate(10);

        return view('registrations.history', compact('registrations'));
    }

    /**
     * Cancel a registration
     */
    public function cancel(Registration $registration)
    {
        // Ensure user can only cancel their own registrations
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to registration.');
        }

        // Check if cancellation is allowed
        if (!$this->canCancelRegistration($registration)) {
            return redirect()->back()
                ->with('error', 'This registration cannot be cancelled at this time.');
        }

        try {
            $registration->update([
                'status' => 'cancelled',
                'admin_notes' => 'Cancelled by user on ' . now()->format('Y-m-d H:i:s'),
            ]);

            // Send cancellation notification
            $this->notificationService->sendRegistrationCancellation($registration);

            return redirect()->route('registrations.history')
                ->with('success', 'Registration cancelled successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel registration. Please try again.');
        }
    }

    /**
     * Show registration confirmation page
     */
    public function confirmation(Registration $registration)
    {
        // Ensure user can only view their own registration confirmations
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to registration.');
        }

        $registration->load(['event', 'event.fest']);

        return view('registrations.confirmation', compact('registration'));
    }

    /**
     * Show team management interface
     */
    public function manageTeam(Registration $registration)
    {
        // Ensure user can only manage their own team registrations
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to team registration.');
        }

        // Ensure this is a team registration
        if ($registration->registration_type !== 'team') {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'This is not a team registration.');
        }

        // Check if team can still be modified
        if (!$this->canModifyTeam($registration)) {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Team cannot be modified at this time.');
        }

        $registration->load(['event', 'user']);
        
        // Get available users for adding to team
        $availableUsers = User::where('id', '!=', Auth::id())
            ->whereNotIn('id', function($query) use ($registration) {
                $query->select('user_id')
                      ->from('registrations')
                      ->where('event_id', $registration->event_id)
                      ->where('status', '!=', 'rejected');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_id']);

        return view('registrations.manage-team', compact('registration', 'availableUsers'));
    }

    /**
     * Add member to team
     */
    public function addTeamMember(Request $request, Registration $registration)
    {
        // Ensure user can only modify their own team registrations
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to team registration.');
        }

        // Check if team can still be modified
        if (!$this->canModifyTeam($registration)) {
            return redirect()->back()
                ->with('error', 'Team cannot be modified at this time.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $newMember = User::findOrFail($validated['user_id']);
            
            // Check if user is already in team
            $teamMembers = $registration->team_members_json ?? [];
            $existingMemberIds = collect($teamMembers)->pluck('user_id')->toArray();
            
            if (in_array($newMember->id, $existingMemberIds) || $newMember->id === $registration->user_id) {
                return redirect()->back()
                    ->with('error', 'User is already in the team.');
            }

            // Add new member to team
            $teamMembers[] = [
                'user_id' => $newMember->id,
                'name' => $newMember->name,
                'email' => $newMember->email,
                'student_id' => $newMember->student_id,
                'role' => 'member',
                'status' => 'pending_invitation'
            ];

            $registration->update(['team_members_json' => $teamMembers]);

            // Send invitation to new member
            $this->notificationService->sendTeamInvitation($registration, $newMember);

            return redirect()->back()
                ->with('success', 'Team member added and invitation sent.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to add team member. Please try again.');
        }
    }

    /**
     * Remove member from team
     */
    public function removeTeamMember(Request $request, Registration $registration)
    {
        // Ensure user can only modify their own team registrations
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to team registration.');
        }

        // Check if team can still be modified
        if (!$this->canModifyTeam($registration)) {
            return redirect()->back()
                ->with('error', 'Team cannot be modified at this time.');
        }

        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        try {
            $teamMembers = $registration->team_members_json ?? [];
            
            // Remove member from team
            $updatedMembers = collect($teamMembers)->filter(function ($member) use ($validated) {
                return $member['user_id'] != $validated['user_id'];
            })->values()->toArray();

            $registration->update(['team_members_json' => $updatedMembers]);

            return redirect()->back()
                ->with('success', 'Team member removed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to remove team member. Please try again.');
        }
    }

    /**
     * Accept team invitation
     */
    public function acceptTeamInvitation(Request $request, Registration $registration)
    {
        $user = Auth::user();
        
        // Check if user is invited to this team
        $teamMembers = $registration->team_members_json ?? [];
        $memberIndex = collect($teamMembers)->search(function ($member) use ($user) {
            return $member['user_id'] == $user->id;
        });

        if ($memberIndex === false) {
            return redirect()->route('events.show', $registration->event)
                ->with('error', 'You are not invited to this team.');
        }

        // Update member status to accepted
        $teamMembers[$memberIndex]['status'] = 'accepted';
        $registration->update(['team_members_json' => $teamMembers]);

        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Team invitation accepted successfully.');
    }

    /**
     * Decline team invitation
     */
    public function declineTeamInvitation(Request $request, Registration $registration)
    {
        $user = Auth::user();
        
        // Check if user is invited to this team
        $teamMembers = $registration->team_members_json ?? [];
        $memberIndex = collect($teamMembers)->search(function ($member) use ($user) {
            return $member['user_id'] == $user->id;
        });

        if ($memberIndex === false) {
            return redirect()->route('events.show', $registration->event)
                ->with('error', 'You are not invited to this team.');
        }

        // Remove member from team
        $updatedMembers = collect($teamMembers)->filter(function ($member) use ($user) {
            return $member['user_id'] != $user->id;
        })->values()->toArray();

        $registration->update(['team_members_json' => $updatedMembers]);

        return redirect()->route('events.show', $registration->event)
            ->with('success', 'Team invitation declined.');
    }

    /**
     * Send team invitations to all pending members
     */
    private function sendTeamInvitations(Registration $registration)
    {
        $teamMembers = $registration->team_members_json ?? [];
        
        foreach ($teamMembers as $member) {
            if ($member['status'] === 'pending_invitation') {
                $user = User::find($member['user_id']);
                if ($user) {
                    $this->notificationService->sendTeamInvitation($registration, $user);
                }
            }
        }
    }

    /**
     * Check if team can be modified
     */
    private function canModifyTeam(Registration $registration): bool
    {
        // Cannot modify if registration is approved or rejected
        if (in_array($registration->status, ['approved', 'rejected', 'cancelled'])) {
            return false;
        }

        // Cannot modify if registration deadline has passed
        if ($registration->event->registration_deadline && 
            $registration->event->registration_deadline < now()) {
            return false;
        }

        // Cannot modify if event has already started
        if ($registration->event->event_date <= now()->toDateString()) {
            return false;
        }

        return true;
    }

    /**
     * Show payment resubmission form
     */
    public function showPaymentResubmissionForm(Registration $registration)
    {
        // Ensure user can only resubmit their own payment
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to registration.');
        }

        // Check if payment resubmission is allowed
        if (!$this->canResubmitPayment($registration)) {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Payment cannot be resubmitted at this time.');
        }

        $registration->load(['event', 'event.fest']);

        return view('registrations.payment-resubmit', compact('registration'));
    }

    /**
     * Process payment resubmission
     */
    public function resubmitPayment(Request $request, Registration $registration)
    {
        // Ensure user can only resubmit their own payment
        if ($registration->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to registration.');
        }

        // Check if payment resubmission is allowed
        if (!$this->canResubmitPayment($registration)) {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Payment cannot be resubmitted at this time.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bkash,nagad,bank_transfer,cash',
            'transaction_id' => 'required|string|max:255',
            'payment_date' => 'required|date|before_or_equal:today',
        ]);

        try {
            $registration->update([
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'],
                'payment_date' => $validated['payment_date'],
                'payment_status' => 'pending',
                'admin_notes' => ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Payment details resubmitted by user on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Send confirmation notification
            $this->notificationService->sendRegistrationConfirmation($registration);

            return redirect()->route('registrations.show', $registration)
                ->with('success', 'Payment details resubmitted successfully! Your payment is now under review again.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['payment' => 'Failed to resubmit payment details. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show payment history for a registration
     */
    public function paymentHistory(Registration $registration)
    {
        // Ensure user can only view their own payment history or admin access
        if ($registration->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to payment history.');
        }

        $registration->load(['event', 'event.fest', 'user']);

        // Get audit logs for this registration
        $auditLogs = DB::table('registration_audit_logs')
            ->where('registration_id', $registration->id)
            ->whereIn('action', ['payment_approved', 'payment_rejected', 'payment_bulk_approved'])
            ->join('users', 'registration_audit_logs.admin_user_id', '=', 'users.id')
            ->select('registration_audit_logs.*', 'users.name as admin_name')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('registrations.payment-history', compact('registration', 'auditLogs'));
    }

    /**
     * Check if payment can be resubmitted
     */
    private function canResubmitPayment(Registration $registration): bool
    {
        // Can only resubmit if payment is required and rejected
        if (!$registration->payment_required || $registration->payment_status !== 'rejected') {
            return false;
        }

        // Cannot resubmit if registration is already approved or cancelled
        if (in_array($registration->status, ['approved', 'cancelled'])) {
            return false;
        }

        // Cannot resubmit if registration deadline has passed
        if ($registration->event->registration_deadline && 
            $registration->event->registration_deadline < now()) {
            return false;
        }

        // Cannot resubmit if event has already started
        if ($registration->event->event_date <= now()->toDateString()) {
            return false;
        }

        return true;
    }

    /**
     * Check if a registration can be cancelled
     */
    private function canCancelRegistration(Registration $registration): bool
    {
        // Cannot cancel if already cancelled or rejected
        if (in_array($registration->status, ['cancelled', 'rejected'])) {
            return false;
        }

        // Cannot cancel if event has already started
        if ($registration->event->event_date <= now()->toDateString()) {
            return false;
        }

        // Cannot cancel if within 24 hours of event (business rule)
        if ($registration->event->event_date <= now()->addDay()->toDateString()) {
            return false;
        }

        return true;
    }
}