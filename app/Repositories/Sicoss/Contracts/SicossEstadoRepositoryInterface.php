<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossEstadoRepositoryInterface
{
    /**
     * Inicializa el estado de situación para un rango de días.
     *
     * @param int $codigo
     * @param int $min
     * @param int $max
     *
     * @return array
     */
    public function inicializarEstadoSituacion(int $codigo, int $min, int $max): array;

    /**
     * Evalúa la condición de licencia según prioridades.
     *
     * @param int $c1 condición actual
     * @param int $c2 condición tipo de licencia
     *
     * @return int
     */
    public function evaluarCondicionLicencia(int $c1, int $c2): int;

    /**
     * Calcula los cambios de estado en el período.
     *
     * @param array $estado_situacion
     *
     * @return array
     */
    public function calcularCambiosEstado(array $estado_situacion): array;

    /**
     * Calcula los días trabajados según códigos de situación.
     *
     * @param array $estado_situacion
     *
     * @return int
     */
    public function calcularDiasTrabajados(array $estado_situacion): int;

    /**
     * Calcula la revista del legajo basada en los cambios de estado.
     *
     * @param array $cambios_estado
     *
     * @return array
     */
    public function calcularRevistaLegajo(array $cambios_estado): array;

    /**
     * Verifica si un agente tiene importes distintos de cero.
     *
     * @param array $leg
     *
     * @return int
     */
    public function verificarAgenteImportesCero(array $leg): int;
}
