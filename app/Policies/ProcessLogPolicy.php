<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ProcessLog;
use App\Models\User;

class ProcessLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ProcessLog');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('view ProcessLog');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ProcessLog');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('update ProcessLog');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('delete ProcessLog');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ProcessLog');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('restore ProcessLog');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ProcessLog');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('replicate ProcessLog');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ProcessLog');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProcessLog $processlog): bool
    {
        return $user->checkPermissionTo('force-delete ProcessLog');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ProcessLog');
    }
}
