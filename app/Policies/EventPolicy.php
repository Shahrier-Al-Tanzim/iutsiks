<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view published events
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Event $event): bool
    {
        // Anyone can view published events
        if ($event->status === 'published') {
            return true;
        }

        // Only admins and authors can view draft/unpublished events
        return $user && ($user->canManageEvents() || $event->author_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super admins and event admins can create events
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        // Super admins can update any event, authors can update their own
        return $user->isSuperAdmin() || $event->author_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        // Super admins can delete any event, authors can delete their own if no registrations
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $event->author_id === $user->id && $event->registrations()->count() === 0;
    }

    /**
     * Determine whether the user can manage event status.
     */
    public function manageStatus(User $user, Event $event): bool
    {
        // Super admins can manage any event status, authors can manage their own
        return $user->isSuperAdmin() || $event->author_id === $user->id;
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(User $user, Event $event): bool
    {
        // Users can register if event is published and registration is open
        if ($event->status !== 'published' || $event->registration_type === 'on_spot') {
            return false;
        }

        // Check if registration deadline has passed
        if ($event->registration_deadline && now()->isAfter($event->registration_deadline)) {
            return false;
        }

        // Check if user is already registered
        return !$event->registrations()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view registrations for the event.
     */
    public function viewRegistrations(User $user, Event $event): bool
    {
        // Super admins and event admins can view all registrations, authors can view their own event registrations
        return $user->canManageEvents() || $event->author_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->isSuperAdmin();
    }
}
