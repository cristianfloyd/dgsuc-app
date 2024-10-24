<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipArt;
use App\Models\User;

class AfipArtPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipArt');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('view AfipArt');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipArt');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('update AfipArt');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('delete AfipArt');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipArt');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('restore AfipArt');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipArt');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('replicate AfipArt');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipArt');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipArt $afipart): bool
    {
        return $user->checkPermissionTo('force-delete AfipArt');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipArt');
    }
}
