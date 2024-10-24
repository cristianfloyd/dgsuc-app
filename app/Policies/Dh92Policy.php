<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dh92;
use App\Models\User;

class Dh92Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dh92');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('view Dh92');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dh92');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('update Dh92');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('delete Dh92');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dh92');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('restore Dh92');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dh92');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('replicate Dh92');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dh92');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dh92 $dh92): bool
    {
        return $user->checkPermissionTo('force-delete Dh92');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dh92');
    }
}
