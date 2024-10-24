<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipRelacionesActivas;
use App\Models\User;

class AfipRelacionesActivasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('view AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('update AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('delete AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('restore AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('replicate AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipRelacionesActivas $afiprelacionesactivas): bool
    {
        return $user->checkPermissionTo('force-delete AfipRelacionesActivas');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipRelacionesActivas');
    }
}
