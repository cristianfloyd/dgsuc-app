<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipMapucheParaSicoss;
use App\Models\User;

class AfipMapucheParaSicossPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('view AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('update AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('delete AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('restore AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('replicate AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipMapucheParaSicoss $afipmapucheparasicoss): bool
    {
        return $user->checkPermissionTo('force-delete AfipMapucheParaSicoss');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipMapucheParaSicoss');
    }
}
