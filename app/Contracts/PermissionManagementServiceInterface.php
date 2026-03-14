<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface PermissionManagementServiceInterface
{
    /**
     * Asigna roles y permisos iniciales a un usuario según su tipo.
     *
     * @param  User  $user  Usuario al que asignar permisos.
     * @param  string  $userType  Tipo de usuario: admin, report_viewer o workflow_manager.
     *
     * @throws \InvalidArgumentException Si el tipo de usuario no es válido.
     */
    public function assignInitialPermissions(User $user, string $userType): void;

    /**
     * Sincroniza los permisos de un usuario.
     *
     * @param  User  $user  Usuario a actualizar.
     * @param  array<int, string>  $permissions  Lista de nombres de permisos.
     */
    public function syncUserPermissions(User $user, array $permissions): void;

    /**
     * Obtiene los permisos disponibles agrupados por categoría.
     *
     * @return array<string, Collection<int, \Spatie\Permission\Models\Permission>>
     */
    public function getAvailablePermissions(): array;
}
