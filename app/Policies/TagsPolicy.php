<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Tags;
use App\Models\User;

class TagsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Tags');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('view Tags');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Tags');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('update Tags');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('delete Tags');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Tags');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('restore Tags');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Tags');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('replicate Tags');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Tags');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tags $tags): bool
    {
        return $user->checkPermissionTo('force-delete Tags');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Tags');
    }
}
