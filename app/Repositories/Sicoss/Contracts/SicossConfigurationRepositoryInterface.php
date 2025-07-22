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

    /**
     * Obtiene el porcentaje de aporte adicional de jubilación.
     */
    public function getPorcentajeAporteAdicionalJubilacion(): float;

    /**
     * Obtiene si el trabajador es convencionado.
     */
    public function getTrabajadorConvencionado(): int;

    /**
     * Obtiene si se deben considerar asignaciones familiares.
     */
    public function getAsignacionFamiliar(): bool;

    /**
     * Obtiene la cantidad de adherentes SICOSS.
     */
    public function getCantidadAdherentesSicoss(): int;

    /**
     * Obtiene si las horas extras son por novedad.
     */
    public function getHorasExtrasPorNovedad(): int;

    /**
     * Obtiene el código de obra social aporte adicional.
     */
    public function getCodigoObraSocialAporteAdicional(): int;

    /**
     * Obtiene el código de aportes voluntarios.
     */
    public function getAportesVoluntarios(): int;

    /**
     * Obtiene el código de obra social familiar a cargo.
     */
    public function getCodigoObraSocialFamiliarCargo(): int;

    /**
     * Obtiene el código de reparto.
     */
    public function getCodigoReparto(): string;

    /**
     * Obtiene los topes jubilatorios configurados.
     *
     * @return array Array con los topes jubilatorios y otros aportes
     */
    public function getTopes(): array;
}
