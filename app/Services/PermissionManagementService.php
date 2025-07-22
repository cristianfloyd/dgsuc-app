<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionManagementService
{
    /**
     * Asigna roles y permisos iniciales a un usuario.
     */
    public function assignInitialPermissions(User $user, string $userType): void
    {
        DB::transaction(function () use ($user, $userType): void {
            switch ($userType) {
                case 'admin':
                    $user->assignRole('admin');
                    $user->givePermissionTo([
                        'access_admin_panel',
                        'view_reports',
                        'manage_users',
                    ]);
                    break;

                case 'report_viewer':
                    $user->assignRole('report_viewer');
                    $user->givePermissionTo([
                        'view_reports',
                    ]);
                    break;

                case 'workflow_manager':
                    $user->assignRole('workflow_manager');
                    $user->givePermissionTo([
                        'manage_workflow',
                        'view_reports',
                    ]);
                    break;
            }
        });
    }

    /**
     * Sincroniza los permisos de un usuario.
     */
    public function syncUserPermissions(User $user, array $permissions): void
    {
        DB::transaction(function () use ($user, $permissions): void {
            $user->syncPermissions($permissions);
        });
    }

    /**
     * Obtiene todos los permisos disponibles agrupados por panel.
     */
    public function getAvailablePermissions(): array
    {
        return [
            'admin' => Permission::where('name', 'like', 'access_%')->get(),
            'reports' => Permission::where('name', 'like', 'view_%')->get(),
            'workflow' => Permission::where('name', 'like', 'manage_%')->get(),
        ];
    }
}
