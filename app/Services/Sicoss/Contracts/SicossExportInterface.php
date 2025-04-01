<?php

namespace App\Services\Sicoss\Contracts;

use Illuminate\Support\Collection;

/**
 * Interfaz para los servicios de exportación SICOSS
 * Define el contrato que deben cumplir todos los exportadores
 */
interface SicossExportInterface
{
    /**
     * Genera un archivo con los datos proporcionados
     *
     * @param Collection $registros Registros a incluir en el archivo
     * @param string|null $periodoFiscal Periodo fiscal opcional (se extraerá del primer registro si es null)
     * @return string Ruta completa del archivo generado
     */
    public function generarArchivo(Collection $registros, ?string $periodoFiscal = null): string;
}
