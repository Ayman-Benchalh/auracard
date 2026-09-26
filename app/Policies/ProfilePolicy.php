<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the profile (or draft preview).
     */
    public function view(User $user, Profile $profile): bool
    {
        // Owner can always view their own profile
        if ($profile->user_id === $user->id) {
            return true;
        }

        // Admins with profiles.view permission can view
        return $user->hasPermission('profiles.view');
    }

    /**
     * Determine whether the user can preview the draft profile.
     */
    public function preview(User $user, Profile $profile): bool
    {
        // Owner can always preview draft
        if ($profile->user_id === $user->id) {
            return true;
        }

        // Staff with explicit profiles.view permission can inspect
        return $user->hasPermission('profiles.view');
    }

    /**
     * Determine whether the user can update the profile draft.
     */
    public function update(User $user, Profile $profile): bool
    {
        // Only the owner can edit their draft
        return $profile->user_id === $user->id;
    }

    /**
     * Determine whether the user can publish the profile.
     */
    public function publish(User $user, Profile $profile): bool
    {
        // Only the owner can trigger publication
        return $profile->user_id === $user->id;
    }

    /**
     * Determine whether the user can upload or attach media to the profile.
     */
    public function uploadMedia(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete media from the profile.
     */
    public function deleteMedia(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete/archive the profile.
     */
    public function delete(User $user, Profile $profile): bool
    {
        if ($profile->user_id === $user->id) {
            return true;
        }

        return $user->hasRole(RoleName::SUPER_ADMIN);
    }

    /**
     * Determine whether the user can suspend/moderate the profile.
     */
    public function suspend(User $user, Profile $profile): bool
    {
        return $user->hasPermission('profiles.suspend');
    }
}
