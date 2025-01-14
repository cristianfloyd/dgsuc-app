<?php

namespace App\Services\Imports;

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

    public function processRecords(Collection $rows): void
    {
        // Agrupamos por nro_cargo para identificar duplicados
        Log::debug('Grouping records by nro_cargo');
        $groupedByCargo = $rows->groupBy('n_de_cargo');

        foreach ($groupedByCargo as $nroCargo => $records) {
            if ($records->count() > 1) {
                // Guardamos los registros duplicados
                $this->duplicateRecords = $this->duplicateRecords->merge(
                    $records->map(fn($record) => [
                        'nro_cargo' => $nroCargo,
                        'nro_legajo' => $record['legajo']
                    ])
                );
            } else {
                // Guardamos los registros válidos
                $this->validRecords = $this->validRecords->merge($records);
            }
        }
    }

    public function getDuplicateRecords(): Collection
    {
        return $this->duplicateRecords;
    }

    public function getValidRecords(): Collection
    {
        return $this->validRecords;
    }


    /**
     * Valida duplicados en la colección de datos del Excel
     *
     * @param Collection $rows Filas del Excel
     * @throws DuplicateCargoException Si se encuentran duplicados
     */
    public function validateExcelDuplicates(Collection $rows): void
    {
        // Contamos ocurrencias de cada nro_cargo
        $cargos = $rows->pluck('n_de_cargo')->toArray();
        $duplicates = array_filter(
            array_count_values($cargos),
            fn($count) => $count > 1
        );

        if (!empty($duplicates)) {
            throw new DuplicateCargoException(
                'Se encontraron números de cargo duplicados en el archivo: ' .
                implode(', ', array_keys($duplicates)),
                $duplicates
            );
        }
    }
}
