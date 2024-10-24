<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dhb3;
use App\Models\User;

class Dhb3Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dhb3');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('view Dhb3');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dhb3');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('update Dhb3');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('delete Dhb3');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dhb3');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('restore Dhb3');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dhb3');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('replicate Dhb3');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dhb3');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dhb3 $dhb3): bool
    {
        return $user->checkPermissionTo('force-delete Dhb3');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dhb3');
    }
}
