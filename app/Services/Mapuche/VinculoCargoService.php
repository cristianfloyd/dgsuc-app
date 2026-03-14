<?php

declare(strict_types=1);

namespace App\Services\Mapuche;

use App\Data\Mapuche\SacCargoData;
use App\Models\Mapuche\Dh10;
use App\Repositories\Sicoss\Dh03Repository;

final readonly class VinculoCargoService
{
    public function __construct(
        private Dh03Repository $dh03Repository,
    ) {}

    /**
     * Procesa la cadena completa de vínculos para un cargo.
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
            $cargoActual = $this->obtenerSiguienteCargo($cargoActual);
            if (!$cargoActual instanceof \App\Data\Mapuche\SacCargoData) {
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
     * Verifica si un cargo tiene un vínculo válido.
     */
    private function tieneVinculoValido(SacCargoData $cargo): bool
    {
        if ($cargo->nro_cargo === $cargo->vcl_cargo) {
            return false;
        }

        return $this->dh03Repository->esVinculoValido(
            $cargo->fecha_alta?->toDateString() ?? '',
            $cargo->vcl_cargo,
        );
    }

    /**
     * Acumula importes mensuales en el array de acumulados.
     *
     * @param array<int, float|int> &$acumulados Referencia al arreglo de importes acumulados por mes.
     * @param array<int, float|int> $importes Arreglo de importes a sumar por mes (índices de 1 a 12).
     */
    private function acumularImportes(array &$acumulados, array $importes): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $acumulados[$i] += $importes[$i] ?? 0;
        }
    }

    /**
     * Actualiza el arreglo de vínculos mensuales según los importes proporcionados.
     *
     * Recorre los meses (1 a 12) y, si el importe correspondiente al mes es mayor a cero,
     * actualiza el valor de $vinculos para ese mes. Si el valor actual de $vinculos en ese
     * mes está vacío, lo establece a $vinculoActual; de lo contrario, concatena el nuevo
     * vínculo al existente usando '->'.
     *
     * @param array<int, string|int|null> &$vinculos Referencia al arreglo de vínculos mensuales (índices 1 a 12).
     * @param array<int, float|int> $importes Arreglo de importes por mes (índices 1 a 12).
     * @param int $vinculoActual Número del vínculo actual a registrar.
     */
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

    /**
     * Obtiene el siguiente cargo vinculado a un cargo actual.
     *
     * Dado un objeto SacCargoData, verifica si existe un vínculo a otro cargo
     * (a través de la propiedad vcl_cargo). Si el número de cargo vinculado
     * coincide con el del cargo actual, retorna null (ya que no hay siguiente).
     * Si existe un modelo Dh10 para ese número de cargo, lo devuelve transformado
     * en SacCargoData, caso contrario retorna null.
     *
     * @param SacCargoData $cargoActual El cargo actual del cual buscar el siguiente.
     *
     * @return SacCargoData|null El siguiente cargo vinculado, o null si no existe.
     */
    private function obtenerSiguienteCargo(SacCargoData $cargoActual): ?SacCargoData
    {
        $siguienteCargo = $cargoActual->vcl_cargo;

        if ($siguienteCargo === $cargoActual->nro_cargo) {
            return null;
        }

        $modelo = Dh10::query()->find($siguienteCargo);

        if (!$modelo) {
            return null;
        }

        return SacCargoData::fromModel($modelo);
    }
}
