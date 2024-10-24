<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dh89;
use App\Models\User;

class Dh89Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dh89');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('view Dh89');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dh89');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('update Dh89');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('delete Dh89');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dh89');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('restore Dh89');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dh89');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('replicate Dh89');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dh89');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dh89 $dh89): bool
    {
        return $user->checkPermissionTo('force-delete Dh89');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dh89');
    }
}
