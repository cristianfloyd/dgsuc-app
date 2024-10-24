<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dha9;
use App\Models\User;

class Dha9Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dha9');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('view Dha9');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dha9');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('update Dha9');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('delete Dha9');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dha9');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('restore Dha9');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dha9');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('replicate Dha9');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dha9');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dha9 $dha9): bool
    {
        return $user->checkPermissionTo('force-delete Dha9');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dha9');
    }
}
