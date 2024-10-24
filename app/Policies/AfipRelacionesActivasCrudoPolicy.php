<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipRelacionesActivasCrudo;
use App\Models\User;

class AfipRelacionesActivasCrudoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('view AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('update AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('delete AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('restore AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('replicate AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipRelacionesActivasCrudo $afiprelacionesactivascrudo): bool
    {
        return $user->checkPermissionTo('force-delete AfipRelacionesActivasCrudo');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipRelacionesActivasCrudo');
    }
}
