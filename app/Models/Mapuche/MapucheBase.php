<?php

namespace App\Models\Mapuche;

use App\Traits\DynamicConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Services\DatabaseConnectionService;

abstract class MapucheBase extends Model
{
    use DynamicConnectionTrait;

    /**
     * Override del getConnectionName para permitir fallback a conexión fija
     */
    public function getConnectionName(): string
    {
        // Si estamos en el contexto del panel AFIP, usar conexión dinámica
        if ($this->shouldUseDynamicConnection()) {
            // Obtener directamente de la sesión para evitar el bucle
            return Session::get(DatabaseConnectionService::SESSION_KEY) ?? 'pgsql-prod';
        }

        // Fallback a la conexión fija de Mapuche
        return 'pgsql-prod';
    }

    /**
     * Determina si debe usar conexión dinámica basado en el contexto
     */
    protected function shouldUseDynamicConnection(): bool
    {
        // Verificar si estamos en el panel de AFIP
        return str_contains(request()->path(), 'afip-panel') ||
            session()->has('using_dynamic_connection');
    }

    // Propiedades y métodos comunes para todos los modelos Mapuche
}
