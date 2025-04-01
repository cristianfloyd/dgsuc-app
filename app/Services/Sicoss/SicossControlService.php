<?php

namespace App\Services\Sicoss;

use Illuminate\Support\Facades\Log;
use App\Models\ControlCuilsDiferencia;
use App\Traits\MapucheConnectionTrait;
use App\Models\ControlConceptosPeriodo;
use App\Models\ControlAportesDiferencia;
use App\Repositories\Sicoss\SicossRepository;
use App\Models\ControlContribucionesDiferencia;

/**
 * Servicio para controles de SICOSS
 * Contiene toda la lógica de negocio relacionada con los controles
 */
class SicossControlService
{
    use MapucheConnectionTrait;

    
    /**
     * Repositorio para acceso a datos
     */
    protected SicossRepository $repository;

    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(SicossRepository $repository)
    {
        $this->repository = $repository;
        $this->repository->setConnection($this->getConnectionName());
    }

    /**
     * Establece la conexión a utilizar
     */
    public function setConnection(string $connection): void
    {
        $this->repository->setConnection($connection);
    }

    /**
     * Ejecuta todos los controles post-importación
     */
    public function ejecutarControlesPostImportacion(int $year, int $month): array
    {
        Log::info('Iniciando controles SICOSS', ['year' => $year, 'month' => $month]);

        try {
            // Crear tabla temporal necesaria para los controles
            $this->repository->crearTablaDH21Aportes($year, $month);

            // Ejecutar controles individuales
            $this->ejecutarControlAportes();
            $this->ejecutarControlContribuciones($year, $month);
            $this->ejecutarControlCuils($year, $month);

            // Obtener resultados consolidados
            $resultados = [
                'totales' => [
                    'dh21' => [
                        'aportes' => $this->getTotalAportesDH21(),
                        'contribuciones' => $this->getTotalContribucionesDH21(),
                    ],
                    'sicoss' => [
                        'aportes' => $this->getTotalAportesSicoss(),
                        'contribuciones' => $this->getTotalContribucionesSicoss(),
                    ],
                ],
                'aportes_contribuciones' => [
                    'diferencias_por_dependencia' => $this->getDiferenciasPorDependencia(),
                ],
            ];

            return $resultados;
        } catch (\Exception $e) {
            Log::error('Error en controles SICOSS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Ejecuta el control de aportes
     */
    public function ejecutarControlAportes(): array
    {
        try {
            // Crear tabla temporal con totales de DH21
            $this->repository->crearTablaDH21Aportes();

            return [
                'diferencias_de_aportes' => $this->repository->obtenerDiferenciasDeAportes(),
                'diferencias_por_dependencia' => [
                    'diferencias_aportes_dependencia' => $this->repository->getDiferenciasAportesPorDependencia(),
                    'diferencias_contribuciones_dependencia' => $this->repository->getDiferenciasContribucionesPorDependencia(),
                ],
                'totales' => $this->repository->obtenerTotalesAportesContribuciones()
            ];
        } catch (\Exception $e) {
            Log::error('Error en control de aportes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Ejecuta el control de contribuciones
     */
    public function ejecutarControlContribuciones(int $year, int $month): array
    {
        try {
            // Crear tabla temporal necesaria
            $this->repository->crearTablaDH21Aportes($year, $month);

            // Ejecutar control específico de contribuciones
            $diferenciasContribuciones = $this->repository->obtenerDiferenciasDeContribuciones($year, $month);

            return [
                'status' => 'success',
                'message' => 'Control de contribuciones ejecutado correctamente',
            ];
        } catch (\Exception $e) {
            Log::error('Error en control de contribuciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Ejecuta el control de CUILs
     */
    public function ejecutarControlCuils(int $year, int $month): array
    {
        try {
            $datos = $this->repository->ejecutarControlCuils($year, $month);

            return [
                'status' => 'success',
                'message' => 'Control de CUILs ejecutado correctamente',
                'data' => $datos
            ];
        } catch (\Exception $e) {
            Log::error('Error en control de CUILs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Ejecuta el control de conceptos
     */
    public function ejecutarControlConceptos(int $year, int $month): array
    {
        try {
            // Lista de conceptos a controlar
            $conceptos = [
                // Aportes
                '201',
                '202',
                '203',
                '204',
                '205',
                '247',
                '248',
                // Contribuciones
                '301',
                '302',
                '303',
                '304',
                '305',
                '306',
                '307',
                '308',
                '347',
                '348'
            ];

            // Obtener resultados de conceptos
            $resultados = $this->repository->getConceptosPorPeriodo($year, $month, $conceptos);

            // Eliminar registros anteriores para este período
            ControlConceptosPeriodo::where('year', $year)
                ->where('month', $month)
                ->where('connection_name', $this->repository->getConnection())
                ->delete();

            // Guardar los nuevos resultados
            foreach ($resultados as $resultado) {
                ControlConceptosPeriodo::create([
                    'year' => $year,
                    'month' => $month,
                    'codn_conce' => $resultado->codn_conce,
                    'desc_conce' => $resultado->desc_conce,
                    'importe' => $resultado->importe,
                    'connection_name' => $this->repository->getConnection(),
                ]);
            }

            // Calcular totales para el resultado
            $totalAportes = collect($resultados)
                ->whereIn('codn_conce', ['201', '202', '203', '204', '205', '247', '248'])
                ->sum('importe');

            $totalContribuciones = collect($resultados)
                ->whereIn('codn_conce', ['301', '302', '303', '304', '305', '306', '307', '308', '347', '348'])
                ->sum('importe');

            return [
                'status' => 'success',
                'message' => 'Control de conceptos ejecutado correctamente',
                'resultados' => $resultados,
                'totalAportes' => $totalAportes,
                'totalContribuciones' => $totalContribuciones,
            ];
        } catch (\Exception $e) {
            Log::error('Error en control de conceptos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Ejecuta el control de conteos
     */
    public function ejecutarControlConteos(int $year, int $month): array
    {
        try {
            // Crear tabla temporal necesaria para los conteos
            $this->repository->crearTablaDH21Aportes($year, $month);

            // Obtener conteos
            $conteos = $this->repository->getConteos();

            return [
                'status' => 'success',
                'message' => 'Control de conteos ejecutado correctamente',
                'conteos' => $conteos,
            ];
        } catch (\Exception $e) {
            Log::error('Error en control de conteos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Obtiene el resumen de estadísticas
     */
    public function getResumenStats(int $year, int $month): array
    {
        try {
            // Crear tabla temporal necesaria
            $this->repository->crearTablaDH21Aportes($year, $month);

            // Obtener estadísticas del repositorio
            return $this->repository->getResumenStats($year, $month);
        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
    }

    // Métodos auxiliares para obtener totales
    private function getTotalAportesDH21(): float
    {
        $totalesAportes = $this->repository->getTotalAporteDh21();
        return $totalesAportes;
    }

    private function getTotalContribucionesDH21(): float
    {
        return $this->repository->getTotalContribucionesDh21();
    }

    private function getTotalAportesSicoss(): float
    {
        return $this->repository->getTotalAportesSicoss();
    }

    private function getTotalContribucionesSicoss(): float
    {
        return $this->repository->getTotalContribucionesSicoss();
    }

    private function getDiferenciasPorDependencia(): array
    {
        // Implementar según lógica específica
        return $this->repository->getDiferenciasPorDependencia();
    }
}
