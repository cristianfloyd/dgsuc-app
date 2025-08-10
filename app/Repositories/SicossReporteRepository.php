<?php

namespace App\Repositories;

use App\Models\Mapuche\MapucheSicossReporte;
use App\Repositories\Interfaces\SicossReporteRepositoryInterface;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SicossReporteRepository implements SicossReporteRepositoryInterface
{
    protected PeriodoFiscalService $periodoFiscalService;

    /**
     * Constructor del repositorio.
     *
     * @param PeriodoFiscalService $periodoFiscalService Servicio de períodos fiscales
     */
    public function __construct(PeriodoFiscalService $periodoFiscalService)
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }

    /**
     * Obtiene los datos del reporte SICOSS para el período especificado.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return Collection Colección de registros del reporte
     */
    public function getReporte(string $anio, string $mes): Collection
    {
        try {
            return MapucheSicossReporte::query()
                ->getReporte($anio, $mes)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener reporte SICOSS en el repositorio', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $e->getTraceAsString(),
            ]);

            return collect([]);
        }
    }

    /**
     * Obtiene los totales del reporte SICOSS para el período especificado.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return array Totales del reporte
     */
    public function getTotales(string $anio, string $mes): array
    {
        try {
            $resultado = MapucheSicossReporte::query()->getTotales($anio, $mes);

            // Ya verificamos en el modelo que getTotales siempre retorna un array
            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error al obtener totales del reporte SICOSS en el repositorio', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'total_aportes' => 0,
                'total_contribuciones' => 0,
                'total_remunerativo' => 0,
                'total_no_remunerativo' => 0,
                'total_c305' => 0,
                'total_c306' => 0,
            ];
        }
    }

    /**
     * Verifica si existen datos para un período fiscal específico.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return bool True si existen datos para el período, false en caso contrario
     */
    public function existenDatosParaPeriodo(string $anio, string $mes): bool
    {
        try {
            $tablaPeriodo = $this->determinarTablaPeriodo($anio, $mes);

            return MapucheSicossReporte::query()
                ->from($tablaPeriodo)
                ->whereIn('nro_liqui', function ($query) use ($anio, $mes): void {
                    $query->select('nro_liqui')
                        ->from('mapuche.dh22')
                        ->where('sino_genimp', true)
                        ->where('per_liano', $anio)
                        ->where('per_limes', $mes);
                })
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de datos para período', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
            ]);

            return false;
        }
    }

    /**
     * Obtiene los períodos fiscales disponibles en el sistema.
     *
     * @return Collection Períodos fiscales disponibles
     */
    public function getPeriodosFiscalesDisponibles(): Collection
    {
        try {
            return MapucheSicossReporte::query()
                ->select('dh22.per_liano', 'dh22.per_limes')
                ->from('mapuche.dh22')
                ->where('dh22.sino_genimp', true)
                ->groupBy('dh22.per_liano', 'dh22.per_limes')
                ->orderBy('dh22.per_liano', 'desc')
                ->orderBy('dh22.per_limes', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener períodos fiscales disponibles', [
                'error' => $e->getMessage(),
            ]);

            return collect([]);
        }
    }

    /**
     * Determina la tabla de período a utilizar basada en el año y mes proporcionados.
     *
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     *
     * @return string Nombre de la tabla a utilizar
     */
    private function determinarTablaPeriodo(string $anio, string $mes): string
    {
        try {
            $periodoActual = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();

            return ((int)$periodoActual['year'] === (int)$anio && (int)$periodoActual['month'] === (int)$mes)
                ? 'mapuche.dh21'
                : 'mapuche.dh21h';
        } catch (\Exception $e) {
            Log::error('Error al determinar tabla de período', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes,
            ]);
            // En caso de error, usar la tabla histórica por defecto
            return 'mapuche.dh21h';
        }
    }
}
