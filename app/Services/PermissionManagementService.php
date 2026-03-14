<?php

namespace App\Services;

use App\Contracts\PermissionManagementServiceInterface;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Permission\Models\Permission;

class PermissionManagementService implements PermissionManagementServiceInterface
{
    public function assignInitialPermissions(User $user, string $userType): void
    {
        $type = UserType::tryFrom($userType);
        if ($type === null) {
            throw new InvalidArgumentException("Tipo de usuario inválido: {$userType}. Valores esperados: admin, report_viewer, workflow_manager.");
        }

        DB::transaction(function () use ($user, $type): void {
            $user->assignRole($type->role());
            $user->syncPermissions($type->permissions());
        });
    }

    public function syncUserPermissions(User $user, array $permissions): void
    {
        DB::transaction(fn(): mixed => $user->syncPermissions($permissions));
    }

    /**
     * @return array<string, Collection<int, Permission>>
     */
    public function getAvailablePermissions(): array
    {
        return [
            'admin' => Permission::query()->where('name', 'like', 'access_%')->get(),
            'reports' => Permission::query()->where('name', 'like', 'view_%')->get(),
            'workflow' => Permission::query()->where('name', 'like', 'manage_%')->get(),
        ];
    }
}
