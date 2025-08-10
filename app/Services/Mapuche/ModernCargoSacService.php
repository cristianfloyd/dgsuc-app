<?php

declare(strict_types=1);

namespace App\Services\Mapuche;

use App\Data\Mapuche\{SacCargoData, SacLegajoData};
use App\Repositories\Mapuche\SacRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ModernCargoSacService
{
    public function __construct(
        private SacRepository $sacRepository,
        private PeriodoFiscalService $periodoService,
        private VinculoCargoService $vinculoService,
    ) {
    }

    /**
     * Obtiene los datos de brutos SAC para un cargo específico.
     */
    public function obtenerBrutosSacCargo(int $legajo, int $nroCargo): ?SacCargoData
    {
        $datos = $this->sacRepository->getBrutosSacCargo($legajo, $nroCargo);

        if (!$datos) {
            return null;
        }

        return $this->procesarVinculos($datos);
    }

    /**
     * Procesa los datos SAC aplicando filtros y devuelve legajos procesados.
     */
    public function procesarBrutosParaSac(array $filtros, string $orderBy = ''): Collection
    {
        $datosRaw = $this->sacRepository->getBrutosParaSac($filtros, $orderBy);

        return $this->agruparPorLegajo($datosRaw)
            ->map(fn ($grupo) => $this->procesarLegajo($grupo))
            ->values();
    }

    /**
     * Actualiza los importes de un cargo.
     */
    public function actualizarCargo(int $nroCargo, array $datos): bool
    {
        // Validar datos antes de actualizar
        $datosValidados = $this->validarDatosCargo($datos);

        return $this->sacRepository->actualizarCargo($nroCargo, $datosValidados);
    }

    /**
     * Obtiene información de un mes específico con cache.
     */
    public function obtenerInfoMes(int $mes, int $anio): array
    {
        $cacheKey = "info_mes_{$anio}_{$mes}";

        return Cache::remember($cacheKey, 3600, function () use ($mes, $anio) {
            $this->validarParametrosFecha($mes, $anio);

            $fecha = Carbon::createFromDate($anio, $mes, 1);

            return [
                'dias' => $fecha->daysInMonth,
                'nombre' => $fecha->translatedFormat('F'),
                'nombre_corto' => $fecha->translatedFormat('M'),
                'primer_dia' => $fecha->startOfMonth()->translatedFormat('l'),
                'ultimo_dia' => $fecha->endOfMonth()->translatedFormat('l'),
                'es_bisiesto' => $fecha->isLeapYear(),
                'trimestre' => $fecha->quarter,
            ];
        });
    }

    /**
     * Valida parámetros de fecha.
     */
    private function validarParametrosFecha(int $mes, int $anio): void
    {
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        if ($anio < 1900 || $anio > 2100) {
            throw new \InvalidArgumentException("El año debe estar entre 1900 y 2100. Recibido: {$anio}");
        }
    }

    /**
     * Procesa los vínculos de un cargo.
     */
    private function procesarVinculos(SacCargoData $datos): SacCargoData
    {
        if ($datos->nro_cargo === $datos->vcl_cargo) {
            return $datos;
        }

        return $this->vinculoService->procesarCadenaVinculos($datos);
    }

    /**
     * Agrupa los datos por legajo.
     */
    private function agruparPorLegajo(Collection $datos): Collection
    {
        return $datos->groupBy('nro_legaj');
    }

    /**
     * Procesa un legajo individual.
     */
    private function procesarLegajo(Collection $grupoLegajo): SacLegajoData
    {
        $primerRegistro = $grupoLegajo->first();

        return SacLegajoData::from([
            'nro_legajo' => $primerRegistro->nro_legaj,
            'apellido_nombres' => "{$primerRegistro->desc_appat}, {$primerRegistro->desc_nombr}",
            'documento' => $primerRegistro->nro_docum,
            'tipo_documento' => $primerRegistro->tipo_docum,
            'dependencia' => $primerRegistro->codc_uacad,
            'cargos' => $this->procesarCargosDelLegajo($grupoLegajo),
            'cargos_vigentes' => $this->obtenerCargosVigentes($grupoLegajo),
        ]);
    }

    /**
     * Valida los datos del cargo antes de actualizar.
     */
    private function validarDatosCargo(array $datos): array
    {
        $datosValidados = [];

        for ($i = 1; $i <= 12; $i++) {
            $campo = "imp_bruto_{$i}";
            if (isset($datos[$campo])) {
                $datosValidados[$campo] = (float)$datos[$campo];
            }
        }

        return $datosValidados;
    }

    // ... resto de métodos privados de procesamiento
}
