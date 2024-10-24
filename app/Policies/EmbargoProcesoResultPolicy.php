<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\EmbargoProcesoResult;
use App\Models\User;

class EmbargoProcesoResultPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('view EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('update EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('delete EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('restore EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('replicate EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmbargoProcesoResult $embargoprocesoresult): bool
    {
        return $user->checkPermissionTo('force-delete EmbargoProcesoResult');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any EmbargoProcesoResult');
    }
}
