<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Personal;
use App\Models\User;

class PersonalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Personal');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('view Personal');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Personal');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('update Personal');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('delete Personal');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Personal');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('restore Personal');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Personal');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('replicate Personal');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Personal');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Personal $personal): bool
    {
        return $user->checkPermissionTo('force-delete Personal');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Personal');
    }
}
