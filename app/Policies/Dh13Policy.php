<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dh13;
use App\Models\User;

class Dh13Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dh13');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('view Dh13');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dh13');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('update Dh13');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('delete Dh13');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dh13');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('restore Dh13');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dh13');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('replicate Dh13');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dh13');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dh13 $dh13): bool
    {
        return $user->checkPermissionTo('force-delete Dh13');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dh13');
    }
}
