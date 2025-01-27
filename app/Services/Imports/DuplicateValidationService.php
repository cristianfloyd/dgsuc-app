<?php

namespace App\Services\Imports;

use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Exceptions\DuplicateCargoException;

class DuplicateValidationService
{
    private Collection $duplicateRecords;
    private Collection $validRecords;

    public function __construct()
    {
        $this->duplicateRecords = collect();
        $this->validRecords = collect();
        Log::debug('DuplicateValidationService initialized');
    }

    /**
     * Procesa los registros para validar duplicados.
     *
     * Este método agrupa los registros por nro_cargo y marca los duplicados.
     * Los registros únicos se marcan como válidos.
     *
     * @param Collection $rows Los registros a procesar.
     * @return void
     */
    public function processRecords(Collection $rows): void
    {
        Log::debug('Agrupando registros por nro_cargo, en DuplicateValidationService');
        $groupedByCargo = $rows->groupBy('n_de_cargo');

        foreach ($groupedByCargo as $nroCargo => $records) {
            if ($records->count() > 1) {
                // Marcamos todos los registros como duplicados pero los mantenemos
                $markedRecords = $records->map(function ($record) {
                    $record['estado'] = BloqueosEstadoEnum::DUPLICADO;
                    return $record;
                });
                $this->duplicateRecords = $this->duplicateRecords->merge($markedRecords);
                $this->validRecords = $this->validRecords->merge($markedRecords);
            } else {
                // Los registros únicos se marcan como válidos
                $record = $records->first();
                $record['estado'] = BloqueosEstadoEnum::VALIDADO;
                $this->validRecords = $this->validRecords->merge(collect([$record]));
            }
        }
    }

    /**
     * Retorna los registros duplicados.
     *
     * @return Collection Colección de registros duplicados.
     */
    public function getDuplicateRecords(): Collection
    {
        return $this->duplicateRecords;
    }

    /**
     * Retorna los registros válidos.
     *
     * @return Collection Colección de registros válidos.
     */
    public function getValidRecords(): Collection
    {
        return $this->validRecords;
    }

    /**
     * Valida duplicados en la colección de datos del Excel
     * Ahora solo registra los duplicados sin lanzar excepción
     *
     * @param Collection $rows Filas del Excel
     */
    public function validateExcelDuplicates(Collection $rows): void
    {
        $cargos = $rows->pluck('n_de_cargo')->toArray();
        $duplicates = array_filter(
            array_count_values($cargos),
            fn($count) => $count > 1
        );

        if (!empty($duplicates)) {
            Log::info('Se encontraron números de cargo duplicados: ' . implode(', ', array_keys($duplicates)));
        }
    }
}
