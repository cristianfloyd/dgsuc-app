<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dh03;
use App\Models\User;

class Dh03Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dh03');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('view Dh03');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dh03');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('update Dh03');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('delete Dh03');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dh03');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('restore Dh03');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dh03');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('replicate Dh03');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dh03');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dh03 $dh03): bool
    {
        return $user->checkPermissionTo('force-delete Dh03');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dh03');
    }
}
