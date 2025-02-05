<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Contracts\TableService\TableServiceInterface;
use App\Services\TableManager\TableInitializationManager;

/**
 * Trait para manejar la inicialización de tablas en recursos Filament
 */
trait FilamentTableInitializationTrait
{
    /**
     * Inicializa la tabla asociada al recurso
     */
    public static function bootFilamentTableInitializationTrait(): void
    {
        Log::info("Iniciando boot del trait", [
            'class' => static::class,
            'timestamp' => now()
        ]);

        $manager = app(TableInitializationManager::class);
        $service = app(static::getTableServiceClass());

        Log::info("Servicio instanciado", [
            'service_class' => get_class($service),
            'table_name' => $service->getTableName()
        ]);

        if ($service instanceof TableServiceInterface) {
            if (!$manager->isTableInitialized($service)) {
                Log::info("Inicializando tabla nueva");
                $manager->initializeTable($service);
            } else {
                Log::info("Tabla ya existente");
            }
        }
    }

    /**
     * Obtiene la clase del servicio de tabla
     *
     * @return string Nombre completo de la clase del servicio
     */
    // abstract protected function getTableServiceClass(): string;
    abstract public static function getTableServiceClass(): string;
}
