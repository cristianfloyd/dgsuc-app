<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dhe5;
use App\Models\User;

class Dhe5Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dhe5');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('view Dhe5');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dhe5');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('update Dhe5');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('delete Dhe5');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dhe5');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('restore Dhe5');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dhe5');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('replicate Dhe5');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dhe5');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dhe5 $dhe5): bool
    {
        return $user->checkPermissionTo('force-delete Dhe5');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dhe5');
    }
}
