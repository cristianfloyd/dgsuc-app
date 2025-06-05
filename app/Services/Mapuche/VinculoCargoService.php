<?php

declare(strict_types=1);

namespace App\Services\Mapuche;

use App\Data\Mapuche\SacCargoData;
use App\Repositories\Sicoss\Dh03Repository;

class VinculoCargoService
{
    public function __construct(
        private Dh03Repository $dh03Repository
    ) {}

    /**
     * Procesa la cadena completa de vínculos para un cargo
     */
    public function procesarCadenaVinculos(SacCargoData $cargoData): SacCargoData
    {
        $importesAcumulados = array_fill(1, 12, 0);
        $vinculos = array_fill(1, 12, '');

        $cargoActual = $cargoData;
        $vinculoActual = $cargoData->vcl_cargo;

        while ($this->tieneVinculoValido($cargoActual)) {
            $this->acumularImportes($importesAcumulados, $cargoActual->importes_brutos);
            $this->actualizarVinculos($vinculos, $cargoActual->importes_brutos, $vinculoActual);

            if ($cargoActual->nro_cargo === $cargoActual->vcl_cargo) {
                break;
            }

            // Obtener siguiente cargo en la cadena
            $cargoActual = $this->obtenerSiguienteCargo($cargoActual->vcl_cargo);
            if (!$cargoActual) {
                break;
            }

            $vinculoActual = $cargoActual->vcl_cargo;
        }

        return new SacCargoData(
            nro_cargo: $cargoData->nro_cargo,
            vcl_cargo: $cargoData->vcl_cargo,
            importes_brutos: $cargoData->importes_brutos,
            importes_acumulados: $importesAcumulados,
            vinculos: $vinculos,
            primer_semestre: $cargoData->primer_semestre,
            segundo_semestre: $cargoData->segundo_semestre,
            categoria: $cargoData->categoria,
            fecha_alta: $cargoData->fecha_alta,
            fecha_baja: $cargoData->fecha_baja,
        );
    }

    /**
     * Verifica si un cargo tiene un vínculo válido
     */
    private function tieneVinculoValido(SacCargoData $cargo): bool
    {
        if ($cargo->nro_cargo === $cargo->vcl_cargo) {
            return false;
        }

        return $this->dh03Repository->esVinculoValido(
            $cargo->fecha_alta?->toDateString() ?? '',
            $cargo->vcl_cargo
        );
    }

    private function acumularImportes(array &$acumulados, array $importes): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $acumulados[$i] += $importes[$i] ?? 0;
        }
    }

    private function actualizarVinculos(array &$vinculos, array $importes, int $vinculoActual): void
    {
        for ($i = 1; $i <= 12; $i++) {
            if (($importes[$i] ?? 0) > 0) {
                $vinculos[$i] = empty($vinculos[$i])
                    ? $vinculoActual
                    : "{$vinculos[$i]} -> {$vinculoActual}";
            }
        }
    }

    private function obtenerSiguienteCargo(int $nroCargo): ?SacCargoData
    {
        // Implementar lógica para obtener el siguiente cargo en la cadena
        // Esto requeriría acceso al repository o al modelo
        return null;
    }
}