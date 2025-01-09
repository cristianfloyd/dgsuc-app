<?php

namespace App\Services\Imports;

use Illuminate\Support\Collection;
use App\Exceptions\DuplicateCargoException;

class DuplicateValidationService
{
    /**
     * Valida duplicados en la colección de datos del Excel
     */
    public function validateExcelDuplicates(Collection $rows): void
    {
        $cargos = $rows->pluck('n_de_cargo')->toArray();
        $duplicates = array_filter(array_count_values($cargos), fn($count) => $count > 1);

        if (!empty($duplicates)) {
            throw new DuplicateCargoException(
                'Se encontraron números de cargo duplicados en el archivo: ' .
                implode(', ', array_keys($duplicates))
            );
        }
    }
}
