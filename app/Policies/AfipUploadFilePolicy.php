<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipUploadFile;
use App\Models\User;

class AfipUploadFilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipUploadFile');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('view AfipUploadFile');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipUploadFile');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('update AfipUploadFile');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('delete AfipUploadFile');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipUploadFile');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('restore AfipUploadFile');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipUploadFile');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('replicate AfipUploadFile');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipUploadFile');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipUploadFile $afipuploadfile): bool
    {
        return $user->checkPermissionTo('force-delete AfipUploadFile');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipUploadFile');
    }
}
