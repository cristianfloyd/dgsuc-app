<?php

namespace App\Repositories\Sicoss\Contracts;

use App\Data\Sicoss\SicossProcessData;

interface SicossOrchestatorRepositoryInterface
{
    /**
     * Ejecuta el proceso completo de generación SICOSS
     * Orquesta todo el flujo principal según configuración.
     *
     * @param SicossProcessData $datos Datos de configuración
     * @param array $periodo_fiscal Período fiscal
     * @param array $filtros Filtros básicos
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     *
     * @return array
     */
    public function ejecutarProcesoCompleto(
        SicossProcessData $datos,
        array $periodo_fiscal,
        array $filtros,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    ): array;

    /**
     * Procesa SICOSS sin períodos retro
     * Flujo simplificado para período vigente únicamente.
     *
     * @param SicossProcessData $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where_periodo Condición WHERE del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     *
     * @return mixed
     */
    public function procesarSinRetro(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        string $where_periodo,
        string $where,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    );

    /**
     * Procesa SICOSS con períodos retro
     * Flujo complejo que incluye períodos históricos.
     *
     * @param SicossProcessData $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     *
     * @return array
     */
    public function procesarConRetro(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        string $where,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    ): array;

    /**
     * Procesa el resultado final del proceso SICOSS
     * Maneja archivos, paths y resultado según configuración.
     *
     * @param array $totales Totales calculados
     * @param string $testeo_directorio_salida Directorio de testeo
     * @param string $testeo_prefijo_archivos Prefijo de archivos de testeo
     *
     * @return mixed
     */
    public function procesarResultadoFinal(
        array $totales,
        string $testeo_directorio_salida = '',
        string $testeo_prefijo_archivos = '',
    );

    /**
     * Establece el código de reparto para el procesamiento SICOSS.
     *
     * @param string $codc_reparto Código de reparto
     *
     * @return void
     */
    public function setCodigoReparto(string $codc_reparto): void;

    /**
     * Obtiene la lista de archivos generados durante el proceso.
     *
     * @return array Array con los archivos generados por período
     */
    public function getArchivosGenerados(): array;
}
