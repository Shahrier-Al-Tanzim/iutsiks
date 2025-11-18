<?php

namespace App\Policies;

use App\Models\Fest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view published fests, admins can view all
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Fest $fest): bool
    {
        // Anyone can view published fests
        if ($fest->status === 'published') {
            return true;
        }

        // Only admins and creators can view draft/unpublished fests
        return $user && ($user->canManageEvents() || $fest->created_by === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super admins and event admins can create fests
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Fest $fest): bool
    {
        // Super admins can update any fest, creators can update their own
        return $user->isSuperAdmin() || $fest->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fest $fest): bool
    {
        // Super admins can delete any fest, creators can delete their own if no events
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $fest->created_by === $user->id && $fest->events()->count() === 0;
    }

    /**
     * Determine whether the user can manage fest status.
     */
    public function manageStatus(User $user, Fest $fest): bool
    {
        // Super admins can manage any fest status, creators can manage their own
        return $user->isSuperAdmin() || $fest->created_by === $user->id;
    }

    /**
     * Determine whether the user can add events to the fest.
     */
    public function addEvents(User $user, Fest $fest): bool
    {
        // Super admins and event admins can add events, creators can add to their own
        return $user->canManageEvents() || $fest->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Fest $fest): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Fest $fest): bool
    {
        return $user->isSuperAdmin();
    }
}
