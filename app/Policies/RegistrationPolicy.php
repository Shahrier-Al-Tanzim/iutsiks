<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admins and event admins can view all registrations
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Registration $registration): bool
    {
        // Users can view their own registrations, admins can view all
        return $user->id === $registration->user_id || 
               $user->canManageEvents() || 
               $registration->event->author_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create registrations
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registration): bool
    {
        // Users can update their own registrations if status is pending
        if ($user->id === $registration->user_id && $registration->status === 'pending') {
            return true;
        }

        // Admins can update any registration
        return $user->canManageEvents() || $registration->event->author_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registration): bool
    {
        // Users can cancel their own pending registrations
        if ($user->id === $registration->user_id && $registration->status === 'pending') {
            return true;
        }

        // Super admins can delete any registration
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can approve the registration.
     */
    public function approve(User $user, Registration $registration): bool
    {
        // Super admins, event admins, and event authors can approve registrations
        return $user->canManageEvents() || $registration->event->author_id === $user->id;
    }

    /**
     * Determine whether the user can reject the registration.
     */
    public function reject(User $user, Registration $registration): bool
    {
        // Super admins, event admins, and event authors can reject registrations
        return $user->canManageEvents() || $registration->event->author_id === $user->id;
    }

    /**
     * Determine whether the user can verify payment for the registration.
     */
    public function verifyPayment(User $user, Registration $registration): bool
    {
        // Super admins, event admins, and event authors can verify payments
        return $user->canManageEvents() || $registration->event->author_id === $user->id;
    }

    /**
     * Determine whether the user can export registrations.
     */
    public function export(User $user): bool
    {
        // Super admins and event admins can export registrations
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Registration $registration): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Registration $registration): bool
    {
        return $user->isSuperAdmin();
    }
}
