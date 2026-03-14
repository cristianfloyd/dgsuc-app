<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossCalculoRepositoryInterface
{
    /**
     * Sumariza importes de conceptos que pertenecen a un determinado tipo de concepto.
     *
     *
     */
    public function calcularRemunerGrupo(int $nro_legajo, string $tipo, string $where): float;

    /**
     * Calcula horas extras por concepto y cargo.
     *
     *
     */
    public function calculoHorasExtras(int $concepto, int $cargo): array;

    /**
     * Obtiene los importes de otra actividad.
     *
     *
     */
    public function otraActividad(int $nro_legajo): array;

    /**
     * Devuelve el código DGI de obra social correspondiente dado un legajo.
     *
     *
     */
    public function codigoOs(int $nro_legajo): string;
}
