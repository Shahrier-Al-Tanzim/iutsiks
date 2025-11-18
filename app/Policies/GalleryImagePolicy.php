<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GalleryImagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view gallery images
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, GalleryImage $galleryImage): bool
    {
        // Anyone can view gallery images
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins can upload gallery images
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GalleryImage $galleryImage): bool
    {
        // Super admins can update any image, uploaders can update their own
        return $user->isSuperAdmin() || $galleryImage->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GalleryImage $galleryImage): bool
    {
        // Super admins can delete any image, uploaders can delete their own
        return $user->isSuperAdmin() || $galleryImage->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can upload images for events.
     */
    public function uploadForEvent(User $user): bool
    {
        // Super admins and event admins can upload event images
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can upload images for fests.
     */
    public function uploadForFest(User $user): bool
    {
        // Super admins and event admins can upload fest images
        return $user->canManageEvents();
    }

    /**
     * Determine whether the user can upload general gallery images.
     */
    public function uploadGeneral(User $user): bool
    {
        // Any admin can upload general gallery images
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage captions.
     */
    public function manageCaptions(User $user, GalleryImage $galleryImage): bool
    {
        // Super admins can manage any caption, uploaders can manage their own
        return $user->isSuperAdmin() || $galleryImage->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isSuperAdmin();
    }
}
