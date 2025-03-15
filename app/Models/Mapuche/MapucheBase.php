<?php

namespace App\Models\Mapuche;

use App\Traits\DynamicConnectionTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\EnhancedDatabaseConnectionService;

abstract class MapucheBase extends Model
{
    use MapucheConnectionTrait;

    /**
     * Override del getConnectionName para permitir fallback a conexión dinámica
     */
    public function getConnectionName(): string
    {
        // Si estamos en el contexto del panel AFIP, usar conexión dinámica
        if ($this->shouldUseDynamicConnection()) {
            // Usar el servicio mejorado para obtener la conexión
            return app(EnhancedDatabaseConnectionService::class)->getCurrentConnection();
        }

        // Fallback a la conexión fija de Mapuche
        return EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
    }

    /**
     * Determina si debe usar conexión dinámica basado en el contexto
     */
    protected function shouldUseDynamicConnection(): bool
    {
        // Verificar si estamos en el panel de AFIP o cualquier otro contexto que requiera conexión dinámica
        return str_contains(request()->path(), 'afip-panel') ||
            session()->has('using_dynamic_connection') ||
            config('app.use_dynamic_connection', false);
    }
}
