<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TablaTempCuils;
use App\Models\User;

class TablaTempCuilsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TablaTempCuils');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('view TablaTempCuils');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TablaTempCuils');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('update TablaTempCuils');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('delete TablaTempCuils');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TablaTempCuils');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('restore TablaTempCuils');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TablaTempCuils');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('replicate TablaTempCuils');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TablaTempCuils');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TablaTempCuils $tablatempcuils): bool
    {
        return $user->checkPermissionTo('force-delete TablaTempCuils');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TablaTempCuils');
    }
}
