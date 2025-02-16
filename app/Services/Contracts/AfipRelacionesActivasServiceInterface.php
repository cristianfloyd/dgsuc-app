<?php

namespace App\Services\Contracts;

interface AfipRelacionesActivasServiceInterface
{
    public function insertarDatosMasivos(array $datosMapeados, int $chunkSize = 1000): bool;
    public function mapearDatosAlModelo(array $datosProcesados): array;
    public function buscarPorCuil(string $cuil);
    public function obtenerPorPeriodoFiscal(string $periodo);
    public function obtenerEstadisticasPorPeriodo(string $periodo): array;
}
