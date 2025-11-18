<?php

namespace App\Policies;

use App\Models\PrayerTime;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PrayerTimePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view prayer times
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, PrayerTime $prayerTime): bool
    {
        // Anyone can view prayer times
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super admins and content admins can create prayer times
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PrayerTime $prayerTime): bool
    {
        // Super admins and content admins can update prayer times
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PrayerTime $prayerTime): bool
    {
        // Super admins and content admins can delete prayer times
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can manage prayer times.
     */
    public function manage(User $user): bool
    {
        // Super admins and content admins can manage prayer times
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can bulk update prayer times.
     */
    public function bulkUpdate(User $user): bool
    {
        // Super admins and content admins can bulk update prayer times
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PrayerTime $prayerTime): bool
    {
        return $user->canManageContent();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PrayerTime $prayerTime): bool
    {
        return $user->isSuperAdmin();
    }
}
