<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossCalculoRepositoryInterface
{
    /**
     * Sumariza importes de conceptos que pertenecen a un determinado tipo de concepto.
     *
     * @param int $nro_legajo
     * @param string $tipo
     * @param string $where
     *
     * @return float
     */
    public function calcularRemunerGrupo(int $nro_legajo, string $tipo, string $where): float;

    /**
     * Calcula horas extras por concepto y cargo.
     *
     * @param int $concepto
     * @param int $cargo
     *
     * @return array
     */
    public function calculoHorasExtras(int $concepto, int $cargo): array;

    /**
     * Obtiene los importes de otra actividad.
     *
     * @param int $nro_legajo
     *
     * @return array
     */
    public function otraActividad(int $nro_legajo): array;

    /**
     * Devuelve el código DGI de obra social correspondiente dado un legajo.
     *
     * @param int $nro_legajo
     *
     * @return string
     */
    public function codigoOs(int $nro_legajo): string;
}
