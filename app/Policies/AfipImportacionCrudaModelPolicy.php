<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AfipImportacionCrudaModel;
use App\Models\User;

class AfipImportacionCrudaModelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('view AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('update AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('delete AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('restore AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('replicate AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AfipImportacionCrudaModel $afipimportacioncrudamodel): bool
    {
        return $user->checkPermissionTo('force-delete AfipImportacionCrudaModel');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AfipImportacionCrudaModel');
    }
}
