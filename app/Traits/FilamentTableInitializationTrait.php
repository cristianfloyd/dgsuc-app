<?php

namespace App\Traits;

use App\Services\TableManager\TableInitializationManager;

/**
 * Trait para manejar la inicialización de tablas en recursos Filament
 */
trait FilamentTableInitializationTrait
{
    /**
     * Inicializa la tabla asociada al recurso
     */
    protected function initializeTable(): void
    {
        $tableService = app($this->getTableServiceClass());
        app(TableInitializationManager::class)->initializeTable($tableService);
    }

    /**
     * Obtiene la clase del servicio de tabla
     *
     * @return string Nombre completo de la clase del servicio
     */
    abstract protected function getTableServiceClass(): string;
}
