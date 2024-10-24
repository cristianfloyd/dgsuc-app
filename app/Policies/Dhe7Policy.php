<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dhe7;
use App\Models\User;

class Dhe7Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dhe7');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('view Dhe7');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dhe7');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('update Dhe7');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('delete Dhe7');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dhe7');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('restore Dhe7');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dhe7');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('replicate Dhe7');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dhe7');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dhe7 $dhe7): bool
    {
        return $user->checkPermissionTo('force-delete Dhe7');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dhe7');
    }
}
