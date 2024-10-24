<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\SpuDisc;
use App\Models\User;

class SpuDiscPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any SpuDisc');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('view SpuDisc');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create SpuDisc');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('update SpuDisc');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('delete SpuDisc');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any SpuDisc');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('restore SpuDisc');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any SpuDisc');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('replicate SpuDisc');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder SpuDisc');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SpuDisc $spudisc): bool
    {
        return $user->checkPermissionTo('force-delete SpuDisc');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any SpuDisc');
    }
}
