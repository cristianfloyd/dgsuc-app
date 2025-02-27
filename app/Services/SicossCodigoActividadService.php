<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;

class SicossCodigoActividadService
{
    use MapucheConnectionTrait;


    /**
     * Genera la tabla temporal con los conceptos liquidados del período actual
     *
     * @param int $anio Año del período
     * @param int $mes Mes del período
     * @param string $where Condición adicional para filtrar
     * @return bool Éxito de la operación
     */
    public function generarTablaConceptosLiquidados(int $anio, int $mes, string $where = 'true'): bool
    {
        try {
            // Eliminar tablas temporales si existen
            Schema::connection($this->getConnectionName())->dropIfExists('pre_conceptos_liquidados');

            // Crear tabla temporal con conceptos liquidados
            DB::connection($this->getConnectionName())->statement("
                CREATE TEMPORARY TABLE pre_conceptos_liquidados AS
                SELECT
                    -- LIQUIDACION
                    DISTINCT(dh21.id_liquidacion),
                    dh21.impp_conce,
                    dh21.ano_retro,
                    dh21.mes_retro,
                    -- LEGAJO
                    dh01.nro_legaj,
                    -- CONCEPTOS
                    dh21.codn_conce,
                    dh21.tipo_conce,
                    dh21.nro_cargo,
                    dh21.nov1_conce,
                    dh12.nro_orimp,
                    -- Obtengo un arreglo de los tipos de grupo a los que pertenece el concepto
                    (
                        SELECT array(
                            SELECT DISTINCT codn_tipogrupo
                            FROM dh15
                            WHERE dh15.codn_grupo IN (
                                SELECT codn_grupo
                                FROM dh16
                                WHERE dh16.codn_conce = dh21.codn_conce
                            )
                        )
                    ) AS tipos_grupos,
                    codigoescalafon
                FROM dh21
                LEFT OUTER JOIN dh01 ON (dh01.nro_legaj = dh21.nro_legaj)         -- Agentes
                LEFT OUTER JOIN dh12 ON (dh21.codn_conce = dh12.codn_conce)       -- Conceptos de Liquidación
                LEFT OUTER JOIN dh16 ON (dh16.codn_conce = dh12.codn_conce)       -- Grupo al que pertenecen los conceptos
                LEFT OUTER JOIN dh22 ON (dh21.nro_liqui = dh22.nro_liqui)         -- Parámetros de Liquidaciones
                WHERE
                    -- Liquidaciones del período vigente y que generen impuestos
                    dh22.per_liano = ? AND dh22.per_limes = ?
                    AND dh22.sino_genimp
                    AND dh21.codn_conce > 0
                    AND {$where}
            ", [$anio, $mes]);

            // Crear índice para optimizar consultas
            DB::connection($this->getConnectionName())->statement("CREATE INDEX ix_pre_conceptos_liquidados_1 ON pre_conceptos_liquidados(id_liquidacion)");

            return true;
        } catch (\Exception $e) {
            Log::error('Error al generar tabla de conceptos liquidados: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Filtra los conceptos liquidados y genera una tabla específica para un período retro
     *
     * @param string $wherePeriodoRetro Condición para filtrar por período retro
     * @return bool Éxito de la operación
     */
    public function filtrarConceptosPorPeriodoRetro(string $wherePeriodoRetro = 'true'): bool
    {
        try {
            // Eliminar tabla temporal si existe
            Schema::connection($this->getConnectionName())->dropIfExists('conceptos_liquidados');

            // Crear tabla temporal filtrada
            DB::connection($this->getConnectionName())->statement("
                CREATE TEMPORARY TABLE conceptos_liquidados AS
                SELECT *
                FROM pre_conceptos_liquidados t
                WHERE {$wherePeriodoRetro}
            ");

            // Crear índices para optimizar consultas
            DB::connection($this->getConnectionName())->statement("CREATE INDEX ix_conceptos_liquidados_1 ON conceptos_liquidados(nro_legaj,tipos_grupos)");
            DB::connection($this->getConnectionName())->statement("CREATE INDEX ix_conceptos_liquidados_2 ON conceptos_liquidados(nro_legaj,tipo_conce)");

            return true;
        } catch (\Exception $e) {
            Log::error('Error al filtrar conceptos por período retro: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los conceptos liquidados para un legajo específico
     *
     * @param int $nroLegajo Número de legajo
     * @param string $condicion Condición adicional para filtrar los conceptos (opcional)
     * @return array Conceptos liquidados con sus tipos de grupos
     */
    public function obtenerConceptosLiquidados(int $nroLegajo, string $condicion = 'true'): array
    {
        // Utilizamos Query Builder de Laravel para la consulta
        return DB::connection($this->getConnectionName())->table('conceptos_liquidados')
            ->select([
                'impp_conce',
                'nov1_conce',
                'codn_conce',
                'tipos_grupos',
                'nro_cargo',
                'codigoescalafon'
            ])
            ->whereRaw("nro_legaj = ? AND tipos_grupos IS NOT NULL AND {$condicion}", [$nroLegajo])
            ->get()
            ->toArray();

        // Alternativa con SQL directo si es necesario:
        /*
        return DB::select("
            SELECT
                impp_conce,
                nov1_conce,
                codn_conce,
                tipos_grupos,
                nro_cargo,
                codigoescalafon
            FROM
                conceptos_liquidados
            WHERE
                nro_legaj = ?
                AND tipos_grupos IS NOT NULL
                AND {$condicion}
        ", [$nroLegajo]);
        */
    }

    /**
     * Calcula el tipo de actividad para SICOSS basado en los conceptos liquidados
     *
     * @param array $conceptosLiquidados Lista de conceptos liquidados del legajo
     * @param string|null $codigoActividadDefault Código de actividad por defecto de la tabla dha8
     * @return int Código de tipo de actividad para SICOSS
     */
    public function calcularTipoActividad(array $conceptosLiquidados, ?string $codigoActividadDefault = null): int
    {
        // Inicializar la prioridad
        $prioridadTipoActividad = 0;

        // Procesar cada concepto liquidado
        foreach ($conceptosLiquidados as $concepto) {
            // Access properties as object properties, not array indices
            $importe = $concepto->impp_conce;
            $gruposConcepto = $concepto->tipos_grupos;

            // Verificar a qué grupos pertenece el concepto

            // Grupo 11
            if (preg_match('/[^\d]+11[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 38) {
                    $prioridadTipoActividad = 38;
                }
                if (($prioridadTipoActividad == 87) || ($prioridadTipoActividad == 88)) {
                    $prioridadTipoActividad = 38;
                }
            }

            // Grupo 12
            if (preg_match('/[^\d]+12[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 34) {
                    $prioridadTipoActividad = 34;
                }
            }

            // Grupo 13
            if (preg_match('/[^\d]+13[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 35) {
                    $prioridadTipoActividad = 35;
                }
            }

            // Grupo 14
            if (preg_match('/[^\d]+14[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 36) {
                    $prioridadTipoActividad = 36;
                }
                if ($prioridadTipoActividad == 87 || $prioridadTipoActividad == 88) {
                    $prioridadTipoActividad = 36;
                }
            }

            // Grupo 15
            if (preg_match('/[^\d]+15[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 37) {
                    $prioridadTipoActividad = 37;
                }
                if ($prioridadTipoActividad == 87 || $prioridadTipoActividad == 88) {
                    $prioridadTipoActividad = 37;
                }
            }

            // Grupo 48
            if (preg_match('/[^\d]+48[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 36 || $prioridadTipoActividad == 88) {
                    $prioridadTipoActividad = 87;
                }
            }

            // Grupo 49
            if (preg_match('/[^\d]+49[^\d]+/', $gruposConcepto)) {
                if ($prioridadTipoActividad < 36) {
                    $prioridadTipoActividad = 88;
                }
            }
        }

        // Determinar el tipo de actividad final según la prioridad calculada
        if ($prioridadTipoActividad == 38 || $prioridadTipoActividad == 0) {
            return $codigoActividadDefault ?? 0;
        } elseif (($prioridadTipoActividad >= 34 && $prioridadTipoActividad <= 37) ||
                 $prioridadTipoActividad == 87 || $prioridadTipoActividad == 88) {
            return $prioridadTipoActividad;
        }

        // Valor por defecto si no se cumple ninguna condición
        return 0;
    }

    /**
     * Limpia las tablas temporales creadas
     */
    public function limpiarTablasTemporales(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('conceptos_liquidados');
        Schema::connection($this->getConnectionName())->dropIfExists('pre_conceptos_liquidados');
    }
}
