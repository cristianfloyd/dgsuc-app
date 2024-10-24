<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dh99;
use App\Models\User;

class Dh99Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dh99');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('view Dh99');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dh99');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('update Dh99');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('delete Dh99');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dh99');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('restore Dh99');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dh99');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('replicate Dh99');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dh99');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dh99 $dh99): bool
    {
        return $user->checkPermissionTo('force-delete Dh99');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dh99');
    }
}
