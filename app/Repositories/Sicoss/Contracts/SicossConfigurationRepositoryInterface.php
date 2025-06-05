<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossConfigurationRepositoryInterface
{
    /**
     * Carga todas las configuraciones necesarias para SICOSS
     * Extrae el código del método genera_sicoss() tal como está
     */
    public function cargarConfiguraciones(): void;

    /**
     * Obtiene el período fiscal actual (mes y año)
     * Extrae el código del método genera_sicoss() tal como está
     */
    public function obtenerPeriodoFiscal(): array;

    /**
     * Genera los filtros básicos WHERE para consultas
     * Extrae el código del método genera_sicoss() tal como está
     */
    public function generarFiltrosBasicos(array $datos): array;

    /**
     * Inicializa la configuración de archivos y paths para SICOSS
     * Extrae el código del método genera_sicoss() tal como está
     */
    public function inicializarConfiguracionArchivos(): array;
}
