<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dhd7;
use App\Models\User;

class Dhd7Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dhd7');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('view Dhd7');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dhd7');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('update Dhd7');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('delete Dhd7');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Dhd7');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('restore Dhd7');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Dhd7');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('replicate Dhd7');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Dhd7');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dhd7 $dhd7): bool
    {
        return $user->checkPermissionTo('force-delete Dhd7');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Dhd7');
    }
}
