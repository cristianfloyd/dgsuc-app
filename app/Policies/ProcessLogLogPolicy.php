<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ProcessLogLog;
use App\Models\User;

class ProcessLogLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ProcessLogLog');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('view ProcessLogLog');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ProcessLogLog');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('update ProcessLogLog');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('delete ProcessLogLog');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ProcessLogLog');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('restore ProcessLogLog');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ProcessLogLog');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('replicate ProcessLogLog');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ProcessLogLog');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProcessLogLog $processloglog): bool
    {
        return $user->checkPermissionTo('force-delete ProcessLogLog');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ProcessLogLog');
    }
}
