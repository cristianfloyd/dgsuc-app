<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipMapucheMiSimplificacion;
use App\Models\User;

class AfipMapucheMiSimplificacionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('view AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('update AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('delete AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('restore AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('replicate AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipMapucheMiSimplificacion $afipmapuchemisimplificacion): bool
    {
        return $user->checkPermissionTo('force-delete AfipMapucheMiSimplificacion');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipMapucheMiSimplificacion');
    }
}
