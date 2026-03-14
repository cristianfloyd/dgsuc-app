<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossEstadoRepositoryInterface
{
    /**
     * Inicializa el estado de situación para un rango de días.
     */
    public function inicializarEstadoSituacion(int $codigo, int $min, int $max): array;

    /**
     * Evalúa la condición de licencia según prioridades.
     *
     * @param int $c1 condición actual
     * @param int $c2 condición tipo de licencia
     */
    public function evaluarCondicionLicencia(int $c1, int $c2): int;

    /**
     * Calcula los cambios de estado en el período.
     */
    public function calcularCambiosEstado(array $estado_situacion): array;

    /**
     * Calcula los días trabajados según códigos de situación.
     */
    public function calcularDiasTrabajados(array $estado_situacion): int;

    /**
     * Calcula la revista del legajo basada en los cambios de estado.
     */
    public function calcularRevistaLegajo(array $cambios_estado): array;

    /**
     * Verifica si un agente tiene importes distintos de cero.
     */
    public function verificarAgenteImportesCero(array $leg): int;
}
