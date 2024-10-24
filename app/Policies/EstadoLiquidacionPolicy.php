<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\EstadoLiquidacion;
use App\Models\User;

class EstadoLiquidacionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any EstadoLiquidacion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('view EstadoLiquidacion');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create EstadoLiquidacion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('update EstadoLiquidacion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('delete EstadoLiquidacion');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any EstadoLiquidacion');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('restore EstadoLiquidacion');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any EstadoLiquidacion');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('replicate EstadoLiquidacion');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder EstadoLiquidacion');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EstadoLiquidacion $estadoliquidacion): bool
    {
        return $user->checkPermissionTo('force-delete EstadoLiquidacion');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any EstadoLiquidacion');
    }
}
