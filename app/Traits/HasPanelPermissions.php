<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;


trait HasPanelPermissions
{
    use HasRoles;

    /**
     * Define la configuración de paneles y sus permisos requeridos
     */
    protected function getPanelConfigurations(): array
    {
        return [
            'admin' => [
                'permissions' => ['access_admin_panel'],
                'roles' => ['admin', 'super_admin'],
            ],
            'reports' => [
                'permissions' => ['view_reports'],
                'roles' => ['user', 'admin', 'report_viewer'],
            ],
            'workflow' => [
                'permissions' => ['manage_workflow'],
                'roles' => ['workflow_manager', 'admin'],
            ],
        ];
    }

    /**
     * Verifica si el usuario tiene acceso al panel especificado
     */
    public function hasPermissionToAccessPanel(string $panelId): bool
    {
        return Cache::remember("user_{$this->id}_panel_{$panelId}_access", 3600, function () use ($panelId) {
            $config = $this->getPanelConfigurations()[$panelId] ?? null;

            if (!$config) {
                return false;
            }

            // Verifica roles
            $hasRole = empty($config['roles']) || $this->hasAnyRole($config['roles']);

            // Verifica permisos
            $hasPermission = empty($config['permissions']) ||
                            collect($config['permissions'])->every(fn($permission) =>
                                $this->hasPermissionTo($permission));

            return $hasRole && $hasPermission;
        });
    }

    /**
     * Obtiene todos los paneles a los que el usuario tiene acceso
     */
    public function getAccessiblePanels(): array
    {
        return Cache::remember("user_{$this->id}_accessible_panels", 3600, function () {
            return collect($this->getPanelConfigurations())
                ->keys()
                ->filter(fn($panelId) => $this->hasPermissionToAccessPanel($panelId))
                ->values()
                ->toArray();
        });
    }

    /**
     * Verifica si el usuario tiene acceso a múltiples paneles
     */
    public function hasMultiplePanelAccess(): bool
    {
        return count($this->getAccessiblePanels()) > 1;
    }
}
