<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\UploadedFile;
use App\Models\User;

class UploadedFilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any UploadedFile');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('view UploadedFile');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create UploadedFile');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('update UploadedFile');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('delete UploadedFile');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any UploadedFile');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('restore UploadedFile');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any UploadedFile');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('replicate UploadedFile');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder UploadedFile');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UploadedFile $uploadedfile): bool
    {
        return $user->checkPermissionTo('force-delete UploadedFile');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any UploadedFile');
    }
}
