<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossFormateadorRepositoryInterface
{
    /**
     * Formatear un valor numérico llenando con ceros a la izquierda
     *
     * @param mixed $valor Valor a formatear
     * @param int $longitud Longitud total del campo
     * @return string Valor formateado
     */
    public function llenaImportes($valor, int $longitud): string;

    /**
     * Formatear texto rellenando con espacios a la izquierda
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     * @return string Texto formateado
     */
    public function llenaBancosIzq(string $texto, int $longitud): string;

    /**
     * Formatear texto rellenando con espacios a la derecha (conserva iniciales)
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     * @return string Texto formateado
     */
    public function llenaBlancosModificado(string $texto, int $longitud): string;

    /**
     * Formatear texto rellenando con espacios a la derecha
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     * @return string Texto formateado
     */
    public function llenaBlancos(string $texto, int $longitud): string;

    /**
     * Transformar totales a formato recordset para reportes
     *
     * @param array $totalesPeriodo Array con totales por período
     * @return array Recordset formateado
     */
    public function transformarARecordset(array $totalesPeriodo): array;
}
