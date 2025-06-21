<?php

namespace App\Models;

use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheBase;
use Illuminate\Support\Facades\Log;
use App\Traits\HasFixedWithImportes;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

/**
 * Modelo AfipMapucheSicoss
 *
 * Representa los datos de AFIP Mapuche SICOSS en la base de datos.
 */
class AfipMapucheSicoss extends Model
{
    use HasFactory;
    use HasCompositeRelations;
    use MapucheConnectionTrait;
    use HasFixedWithImportes;




    // Especificar la tabla
    protected $table = 'suc.afip_mapuche_sicoss';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;


    // Agregar las columnas que pueden ser asignadas masivamente
    protected $fillable = [
        'periodo_fiscal',
        'cuil',
        'apnom',
        'conyuge',
        'cant_hijos',
        'cod_situacion',
        'cod_cond',
        'cod_act',
        'cod_zona',
        'porc_aporte',
        'cod_mod_cont',
        'cod_os',
        'cant_adh',
        'rem_total',
        'rem_impo1',
        'asig_fam_pag',
        'aporte_vol',
        'imp_Adic_os',
        'exc_aport_ss',
        'exc_aport_os',
        'prov',
        'rem_Impo2',
        'rem_Impo3',
        'rem_Impo4',
        'cod_siniestrado',
        'marca_reduccion',
        'recomp_lrt',
        'tipo_empresa',
        'aporte_adic_os',
        'regimen',
        'sit_rev1',
        'dia_ini_sit_rev1',
        'sit_rev2',
        'dia_ini_sit_rev2',
        'sit_rev3',
        'dia_ini_sit_rev3',
        'sueldo_adicc',
        'sac',
        'horas_extras',
        'zona_desfav',
        'vacaciones',
        'cant_dias_trab',
        'rem_impo5',
        'convencionado',
        'rem_impo6',
        'tipo_oper',
        'adicionales',
        'premios',
        'rem_dec_788',
        'rem_imp7',
        'nro_horas_ext',
        'cpto_no_remun',
        'maternidad',
        'rectificacion_remun',
        'rem_Imp9',
        'contrib_dif',
        'hstrab',
        'seguro',
        'ley',
        'incsalarial',
        'remimp11',
    ];



    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'conyuge' => 'boolean',
        'cant_hijos' => 'integer',
        'rem_total' => 'decimal:2',
        'asig_fam_pag' => 'decimal:2',
        'cod_siniestrado' => 'string',
        'marca_reduccion' => 'string',
        'recomp_lrt' => 'decimal:2',
        'tipo_empresa' => 'string',
        'aporte_adic_os' => 'decimal:2',
        'regimen' => 'string',
        'sit_rev1' => 'string',
        'dia_ini_sit_rev1' => 'integer',
        'sit_rev2' => 'string',
        'dia_ini_sit_rev2' => 'integer',
        'sit_rev3' => 'string',
        'dia_ini_sit_rev3' => 'integer',
        'sueldo_adicc' => 'decimal:2',
        'horas_extras' => 'decimal:2',
        'zona_desfav' => 'decimal:2',
        'vacaciones' => 'decimal:2',
        'cant_dias_trab' => 'integer',
        'convencionado' => 'boolean',
        'tipo_oper' => 'string',
        'adicionales' => 'decimal:2',
        'premios' => 'decimal:2',
        'rem_dec_788' => 'decimal:2',
        'nro_horas_ext' => 'integer',
        'cpto_no_remun' => 'decimal:2',
        'maternidad' => 'decimal:2',
        'rectificacion_remun' => 'decimal:2',
        'contrib_dif' => 'decimal:2',
        'hstrab' => 'decimal:2',
        'seguro' => 'boolean',
        'ley' => 'decimal:2',
        'incsalarial' => 'decimal:2',
        'remimp11' => 'decimal:2',
    ];



    protected $encodedFields = ['apnom', 'prov'];

    protected $appends = [
        'nro_cuil',
        'diferencia_rem'
    ];

    public function nroCuil(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Asegúrate de que `cuil` no sea null antes de intentar extraer `nro_cuil`
                if ($this->cuil) {
                    // Extrae los 8 dígitos del medio de `cuil`
                    return intval(substr($this->cuil, 2, 8));
                }
                return null;
            }
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            foreach ($model->encodedFields as $field) {
                $model->setAttribute($field, EncodingService::toLatin1($model->getAttribute($field)));
            }
        });

        static::updating(function ($model) {
            foreach ($model->encodedFields as $field) {
                $model->setAttribute($field, EncodingService::toLatin1($model->getAttribute($field)));
            }
        });
    }

    /**
     * Obtener el nombre de la conexión de la base de datos estáticamente
     *
     * @return string
     */
    protected static function getMapucheConnection(): string
    {
        return (new static())->getConnectionName();
    }

    /**
     * Formatea un valor decimal para el archivo SICOSS
     * Elimina el punto decimal y rellena con ceros a la izquierda
     */
    public static function formatearDecimal($valor, $longitud): string
    {
        return str_pad(
            number_format($valor, 2, '', ''),
            $longitud,
            '0',
            STR_PAD_LEFT
        );
    }



    // ####################################### RELACIONES ##############################################################

    public function dh01()
    {
        return $this->belongsTo(Dh01::class, 'nro_cuil', 'nro_cuil');
    }

    /**
     * Obtiene el registro por período fiscal y CUIL.
     *
     * @param string $periodoFiscal
     * @param string $cuil
     * @return AfipMapucheSicoss|null
     */
    public static function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss
    {
        return static::where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $cuil)
            ->first();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('cuil', 'ilike', '%' . $search . '%')
            ->orWhere('apnom', 'ilike', "%$search%");
    }

    // Agregar un nuevo método para obtener el periodo fiscal formateado si es necesario
    public function getPeriodoFiscalFormateado()
    {
        $periodo = $this->attributes['periodo_fiscal'];
        return substr($periodo, 0, 4) . '-' . substr($periodo, 4, 2);
    }

    // ###########################################################################################
    // ################################ MUTADORES Y ACCESORES ####################################

    // === BLOQUE DE ACCESSORS Y MUTATORS PARA CAMPOS DE ANCHO FIJO DECIMAL ===

    protected function remTotalDecimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_total');
    }
    protected function remTotalFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_total');
    }

    protected function remImpo1Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_impo1');
    }
    protected function remImpo1Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_impo1');
    }

    protected function asigFamPagDecimal(): Attribute
    {
        return $this->fixedWidthImporte('asig_fam_pag');
    }
    protected function asigFamPagFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('asig_fam_pag');
    }

    protected function aporteVolDecimal(): Attribute
    {
        return $this->fixedWidthImporte('aporte_vol');
    }
    protected function aporteVolFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('aporte_vol');
    }

    protected function impAdicOsDecimal(): Attribute
    {
        return $this->fixedWidthImporte('imp_Adic_os');
    }
    protected function impAdicOsFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('imp_Adic_os');
    }

    protected function remImpo2Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_Impo2');
    }
    protected function remImpo2Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_Impo2');
    }

    protected function remImpo3Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_Impo3');
    }
    protected function remImpo3Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_Impo3');
    }

    protected function remImpo4Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_Impo4');
    }
    protected function remImpo4Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_Impo4');
    }

    protected function recompLrtDecimal(): Attribute
    {
        return $this->fixedWidthImporte('recomp_lrt');
    }
    protected function recompLrtFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('recomp_lrt');
    }

    protected function sueldoAdiccDecimal(): Attribute
    {
        return $this->fixedWidthImporte('sueldo_adicc');
    }
    protected function sueldoAdiccFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('sueldo_adicc');
    }

    protected function sacDecimal(): Attribute
    {
        return $this->fixedWidthImporte('sac');
    }
    protected function sacFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('sac');
    }

    protected function horasExtrasDecimal(): Attribute
    {
        return $this->fixedWidthImporte('horas_extras');
    }
    protected function horasExtrasFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('horas_extras');
    }

    protected function zonaDesfavDecimal(): Attribute
    {
        return $this->fixedWidthImporte('zona_desfav');
    }
    protected function zonaDesfavFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('zona_desfav');
    }

    protected function vacacionesDecimal(): Attribute
    {
        return $this->fixedWidthImporte('vacaciones');
    }
    protected function vacacionesFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('vacaciones');
    }

    protected function remImpo5Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_impo5');
    }
    protected function remImpo5Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_impo5');
    }

    protected function remImpo6Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_impo6');
    }
    protected function remImpo6Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_impo6');
    }

    protected function adicionalesDecimal(): Attribute
    {
        return $this->fixedWidthImporte('adicionales');
    }
    protected function adicionalesFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('adicionales');
    }

    protected function premiosDecimal(): Attribute
    {
        return $this->fixedWidthImporte('premios');
    }
    protected function premiosFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('premios');
    }

    protected function remDec788Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_dec_788');
    }
    protected function remDec788Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_dec_788');
    }

    protected function remImp7Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_imp7');
    }
    protected function remImp7Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_imp7');
    }

    protected function cptoNoRemunDecimal(): Attribute
    {
        return $this->fixedWidthImporte('cpto_no_remun');
    }
    protected function cptoNoRemunFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('cpto_no_remun');
    }

    protected function maternidadDecimal(): Attribute
    {
        return $this->fixedWidthImporte('maternidad');
    }
    protected function maternidadFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('maternidad');
    }

    protected function rectificacionRemunDecimal(): Attribute
    {
        return $this->fixedWidthImporte('rectificacion_remun');
    }
    protected function rectificacionRemunFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rectificacion_remun');
    }

    protected function remImp9Decimal(): Attribute
    {
        return $this->fixedWidthImporte('rem_Imp9');
    }
    protected function remImp9Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('rem_Imp9');
    }

    protected function leyDecimal(): Attribute
    {
        return $this->fixedWidthImporte('ley');
    }
    protected function leyFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('ley');
    }

    protected function incsalarialDecimal(): Attribute
    {
        return $this->fixedWidthImporte('incsalarial');
    }
    protected function incsalarialFixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('incsalarial');
    }

    protected function remimp11Decimal(): Attribute
    {
        return $this->fixedWidthImporte('remimp11');
    }
    protected function remimp11Fixed(): Attribute
    {
        return $this->fixedWidthImporteFixed('remimp11');
    }

    // === FIN DEL BLOQUE DE CAMPOS DE ANCHO FIJO DECIMAL ===


    /**
     * Mutador y Accesor para el campo apnom
     */
    protected function apnom(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8($value),
            set: fn($value) => EncodingService::toLatin1($value)
        );
    }

    protected function remTotal(): Attribute
    {
        return Attribute::make(
            get: fn($value) => trim($value),
            set: fn($value) => trim($value),
        );
    }

    protected function remImpo6(): Attribute
    {
        return Attribute::make(
            get: fn($value) => trim($value),
            set: fn($value) => trim($value),
        );
    }

    protected function remImpo9(): Attribute
    {
        return Attribute::make(
            get: fn($value) => trim($value),
            set: fn($value) => trim($value),
        );
    }

    /**
     * Mutador y Accesor para el campo prov
     */
    protected function prov(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8($value),
            set: fn($value) => EncodingService::toLatin1($value)
        );
    }

    protected function diferenciaRem(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Convertir a números y manejar valores nulos o vacíos
                $remTotal = is_numeric($this->rem_total) ? (float)$this->rem_total : 0;
                $remImpo6 = is_numeric($this->rem_impo6) ? (float)$this->rem_impo6 : 0;

                return $remTotal - $remImpo6;
            },
        );
    }

    // ###########################################################################################
    // ################################ HELPER FUNCTIONS #########################################

    /**
     * Poblar tabla SICOSS con datos del período
     *
     * @param string $periodoFiscal Formato: YYYYMM
     * @param bool $includeInactive Incluir empleados inactivos
     * @param callable|null $progressCallback Callback para progreso
     * @return array
     */
    public static function poblarTablaSicoss(string $periodoFiscal, bool $includeInactive = false, ?callable $progressCallback = null): array
    {
        try {
            $connection = DB::connection(self::getMapucheConnection());
            $connection->beginTransaction();

            // Extraer año y mes del periodo fiscal
            $anio = substr($periodoFiscal, 0, 4);
            $mes = substr($periodoFiscal, -2);
            $fechaInicio = "$anio-$mes-01";
            $fechaFin = date('Y-m-t', strtotime($fechaInicio));

            // 1. Obtener configuración
            $config = self::getConfiguracionSicoss();

            // 2. Limpiar registros existentes del período
            self::where('periodo_fiscal', $periodoFiscal)->delete();

            // Consulta principal
            $sql = "
            WITH licencias AS (
                SELECT
                    dh05.nro_legaj,
                    SUM(CASE
                        WHEN dl02.es_maternidad THEN 1
                        WHEN dh05.nrovarlicencia IN (
                            SELECT unnest(string_to_array(:variantes_vacaciones, ','))::integer
                        ) THEN 1
                        WHEN dh05.nrovarlicencia IN (
                            SELECT unnest(string_to_array(:variantes_protecintegral, ','))::integer
                        ) THEN 1
                        ELSE 0
                    END) as dias_licencia
                FROM mapuche.dh05
                LEFT JOIN mapuche.dl02 ON dh05.nrovarlicencia = dl02.nrovarlicencia
                WHERE dh05.fec_desde <= :fecha_fin::date
                AND (dh05.fec_hasta is null OR dh05.fec_hasta >= :fecha_inicio::date)
                GROUP BY dh05.nro_legaj
            ),
            conceptos_liquidados AS (
                SELECT
                    dh21.nro_legaj,
                    SUM(CASE WHEN tipos_grupos && ARRAY[9] THEN impp_conce ELSE 0 END) as importe_sac,
                    SUM(CASE WHEN tipos_grupos && ARRAY[11,12,13,14,15,48,49] THEN impp_conce ELSE 0 END) as importe_imponible_6,
                    SUM(CASE WHEN tipos_grupos && ARRAY[21] THEN impp_conce ELSE 0 END) as importe_adicionales,
                    SUM(CASE WHEN tipos_grupos && ARRAY[22] THEN impp_conce ELSE 0 END) as importe_premios
                FROM mapuche.dh21
                JOIN mapuche.dh22 ON dh21.nro_liqui = dh22.nro_liqui
                CROSS JOIN LATERAL (
                    SELECT array_agg(DISTINCT dh15.codn_tipogrupo) as tipos_grupos
                    FROM mapuche.dh16
                    JOIN mapuche.dh15 ON dh15.codn_grupo = dh16.codn_grupo
                    WHERE dh16.codn_conce = dh21.codn_conce
                ) grupos
                WHERE dh22.per_liano = :anio::integer
                AND dh22.per_limes = :mes::integer
                GROUP BY dh21.nro_legaj
            )
            SELECT DISTINCT
                dh01.nro_legaj,
                dh01.nro_cuil1,
                dh01.nro_cuil,
                dh01.nro_cuil2,
                dh01.desc_appat || ' ' || dh01.desc_nombr AS apyno,
                dh01.tipo_estad AS estado,
                COALESCE((
                    SELECT COUNT(*)
                    FROM mapuche.dh02
                    WHERE dh02.nro_legaj = dh01.nro_legaj
                    AND dh02.sino_cargo != 'N'
                    AND dh02.codc_paren = 'CONY'
                ), 0) as conyugue,
                COALESCE((
                    SELECT COUNT(*)
                    FROM mapuche.dh02
                    WHERE dh02.nro_legaj = dh01.nro_legaj
                    AND dh02.sino_cargo != 'N'
                    AND dh02.codc_paren IN ('HIJO','HIJN','HINC','HINN')
                ), 0) as hijos,
                dha8.ProvinciaLocalidad,
                COALESCE(NULLIF(dha8.codigosituacion, ''), NULL)::integer as codigosituacion,
                COALESCE(NULLIF(dha8.CodigoCondicion, ''), NULL)::integer as CodigoCondicion,
                COALESCE(NULLIF(dha8.codigozona, ''), NULL)::integer as codigozona,
                COALESCE(NULLIF(dha8.CodigoActividad, ''), NULL)::integer as CodigoActividad,
                dha8.porcaporteadicss AS aporteAdicional,
                dha8.trabajador_convencionado AS trabajadorconvencionado,
                COALESCE(NULLIF(dha8.codigomodalcontrat, ''), NULL)::integer as codigocontratacion,
                dh09.codc_bprev,
                dh09.cant_cargo AS adherentes,
                COALESCE(l.dias_licencia, 0) as dias_licencia,
                COALESCE(cl.importe_sac, 0) as sac,
                COALESCE(cl.importe_imponible_6, 0) as rem_impo6,
                COALESCE(cl.importe_adicionales, 0) as adicionales,
                COALESCE(cl.importe_premios, 0) as premios
            FROM mapuche.dh01
            LEFT JOIN mapuche.dha8 ON dha8.nro_legajo = dh01.nro_legaj
            LEFT JOIN mapuche.dh09 ON dh09.nro_legaj = dh01.nro_legaj
            LEFT JOIN licencias l ON l.nro_legaj = dh01.nro_legaj
            LEFT JOIN conceptos_liquidados cl ON cl.nro_legaj = dh01.nro_legaj
            WHERE " . ($includeInactive ? 'true' : "dh01.tipo_estad = 'A'");

            // Ejecutar la consulta
            $results = DB::connection(self::getMapucheConnection())
                ->select($sql, [
                    'variantes_vacaciones' => config('mapuche.licencias.vacaciones'),
                    'variantes_protecintegral' => config('mapuche.licencias.proteccion_integral'),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'mes' => $mes,
                    'anio' => $anio
                ]);

            // Insertar los resultados en la tabla
            foreach ($results as $result) {
                self::connection(self::getMapucheConnection())
                    ->create([
                        'periodo_fiscal' => $periodoFiscal,
                        'cuil' => $result->nro_cuil,
                        'apnom' => $result->apyno,
                        'conyuge' => (int)$result->conyugue,
                        'cant_hijos' => (int)$result->hijos,
                        'cod_situacion' => (int)$result->codigosituacion,
                        'cod_cond' => (int)$result->CodigoCondicion,
                        'cod_act' => (int)$result->CodigoActividad,
                        'cod_zona' => (int)$result->codigozona,
                        'porc_aporte' => (float)$result->aporteAdicional,
                        'cod_mod_cont' => (int)$result->codigocontratacion,
                        'cod_os' => $result->codc_bprev,
                        'cant_adh' => (int)$result->adherentes,
                        'rem_total' => (float)$result->sac,
                        'rem_impo1' => (float)$result->rem_impo6,
                        'asig_fam_pag' => (float)$result->asig_fam_pag,
                        'aporte_vol' => (float)$result->aporteAdicional,
                        'convencionado' => $result->trabajadorconvencionado,
                        'dias_trabajados' => 30 - $result->dias_licencia
                    ]);
            }

            $connection->commit();
            return ['status' => 'success', 'message' => 'Datos insertados correctamente.'];
        } catch (\Exception $e) {
            Log::error("SICOSS: Error al poblar tabla: " . $e->getMessage());
            if (isset($connection)) {
                $connection->rollBack();
            }
            throw new \Exception("Error al poblar tabla SICOSS: " . $e->getMessage());
        }
    }


    /**
     * Obtener consulta SQL para legajos SICOSS
     *
     * @param string $where Condiciones adicionales WHERE
     * @return string
     */
    public static function getSqlLegajos(string $where = 'true'): string
    {
        return "
        SELECT DISTINCT
            dh01.nro_legaj,
            dh01.nro_cuil1,
            dh01.nro_cuil,
            dh01.nro_cuil2,
            dh01.desc_appat||' '||dh01.desc_nombr AS apyno,
            dh01.tipo_estad AS estado,
            (SELECT COUNT(*)
             FROM mapuche.dh02
             WHERE dh02.nro_legaj = dh01.nro_legaj
             AND dh02.sino_cargo!='N'
             AND dh02.codc_paren ='CONY') as conyugue,
            (SELECT COUNT(*)
             FROM mapuche.dh02
             WHERE dh02.nro_legaj = dh01.nro_legaj
             AND dh02.sino_cargo!='N'
             AND dh02.codc_paren IN ('HIJO','HIJN','HINC','HINN')) as hijos,
            dha8.ProvinciaLocalidad,
            dha8.codigosituacion,
            dha8.CodigoCondicion,
            dha8.codigozona,
            dha8.CodigoActividad,
            dha8.porcaporteadicss AS aporteAdicional,
            dha8.trabajador_convencionado AS trabajadorconvencionado,
            dha8.codigomodalcontrat AS codigocontratacion,
            dh09.codc_bprev,
            dh09.cant_cargo AS adherentes
        FROM mapuche.dh01
        LEFT JOIN mapuche.dha8 ON dha8.nro_legajo = dh01.nro_legaj
        LEFT JOIN mapuche.dh09 ON dh09.nro_legaj = dh01.nro_legaj
        WHERE $where";
    }

    /**
     * Calcular importes para un legajo
     *
     * @param int $nroLegaj
     * @param int $mes
     * @param int $anio
     * @return array
     */
    protected static function calculateImportes(int $nroLegaj, int $mes, int $anio): array
    {
        $sql = "
    WITH conceptos_liquidados AS (
        SELECT
            dh21.codn_conce,
            dh21.impp_conce,
            (SELECT array(
                SELECT DISTINCT codn_tipogrupo
                FROM mapuche.dh15
                WHERE dh15.codn_grupo IN (
                    SELECT codn_grupo
                    FROM mapuche.dh16
                    WHERE dh16.codn_conce = dh21.codn_conce
                )
            )) as tipos_grupos
        FROM mapuche.dh21
        INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
        WHERE dh21.nro_legaj = :nro_legaj
        AND dh22.per_limes = :mes
        AND dh22.per_liano = :anio
    )
    SELECT
        SUM(CASE WHEN :any(tipos_grupos, '{9}') THEN impp_conce ELSE 0 END) as importe_sac,
        SUM(CASE WHEN :any(tipos_grupos, '{11,12,13,14,15,48,49}') THEN impp_conce ELSE 0 END) as importe_imponible_6,
        SUM(CASE WHEN :any(tipos_grupos, '{21}') THEN impp_conce ELSE 0 END) as importe_adicionales,
        SUM(CASE WHEN :any(tipos_grupos, '{22}') THEN impp_conce ELSE 0 END) as importe_premios
    FROM conceptos_liquidados";

        $result = DB::connection(self::getMapucheConnection())->selectOne($sql, [
            'nro_legaj' => $nroLegaj,
            'mes' => $mes,
            'anio' => $anio
        ]);

        return [
            'sac' => $result->importe_sac ?? 0,
            'rem_impo6' => $result->importe_imponible_6 ?? 0,
            'adicionales' => $result->importe_adicionales ?? 0,
            'premios' => $result->importe_premios ?? 0
        ];
    }

    /**
     * Procesar datos de SICOSS para un período específico
     *
     * @param string $periodoFiscal Formato: YYYYMM
     * @param bool $includeInactive Incluir empleados inactivos
     * @return void
     */
    public static function procesarPeriodo(string $periodoFiscal, bool $includeInactive = false): void
    {
        $config = self::getConfiguracionSicoss();
        $where = $includeInactive ? 'true' : "dh01.tipo_estad = 'A'";

        $mes = substr($periodoFiscal, -2);
        $anio = substr($periodoFiscal, 0, 4);

        $legajos = DB::connection(self::getMapucheConnection())->select(self::getSqlLegajos($where));

        foreach ($legajos as $legajo) {
            $importes = self::calculateImportes($legajo->nro_legaj, $mes, $anio);

            // Ajustar ImporteImponible6 según porcentaje diferencial
            if ($importes['rem_impo6'] > 0 && $config['porc_aporte_diferencial'] > 0) {
                $importes['rem_impo6'] = round(
                    ($importes['rem_impo6'] * 100) / $config['porc_aporte_diferencial'],
                    2
                );
            }

            self::updateOrCreate(
                [
                    'periodo_fiscal' => $periodoFiscal,
                    'cuil' => $legajo->nro_cuil1 . str_pad($legajo->nro_cuil, 8, '0', STR_PAD_LEFT) . $legajo->nro_cuil2
                ],
                array_merge([
                    'apnom' => $legajo->apyno,
                    'conyuge' => $legajo->conyugue > 0,
                    'cant_hijos' => $legajo->hijos,
                    'cod_situacion' => $legajo->codigosituacion,
                    'cod_cond' => $legajo->CodigoCondicion,
                    'cod_act' => $legajo->CodigoActividad,
                    'cod_zona' => $legajo->codigozona,
                    'porc_aporte' => $legajo->aporteAdicional,
                    'cod_mod_cont' => $legajo->codigocontratacion,
                    'tipo_empresa' => $config['tipo_empresa'],
                    'regimen' => $legajo->codc_bprev === $config['codc_reparto'] ? '1' : '0',
                    'sit_rev1' => $legajo->codigosituacion,
                    'dia_ini_sit_rev1' => '01',
                    'convencionado' => $legajo->trabajadorconvencionado
                ], $importes)
            );
        }
    }

    /**
     * Obtener parámetros de configuración para SICOSS
     *
     * @return array
     */
    public static function getConfiguracionSicoss(): array
    {
        return [
            'tipo_empresa' => MapucheConfig::getParametroRrhh('Sicoss', 'TipoEmpresa', 'K'),
            'porc_aporte_diferencial' => MapucheConfig::getPorcentajeAporteDiferencialJubilacion(),
            'informar_becarios' => MapucheConfig::getSicossInformarBecarios(),
            'art_con_tope' => MapucheConfig::getSicossArtTope(),
            'conceptos_no_remun_art' => MapucheConfig::getSicossConceptosNoRemunerativosEnArt(),
            'categorias_aportes_diferenciales' => MapucheConfig::getSicossCategoriasAportesDiferenciales(),
            'hs_extras_novedades' => MapucheConfig::getSicossHorasExtrasNovedades(),
            'codc_reparto' => MapucheConfig::getParametroRrhh('Sicoss', 'CodRegimenReparto', '1'),
        ];
    }

    /**
     * Procesar datos de SICOSS para el período actual
     *
     * @param string $periodoFiscal
     * @return void
     */
    public static function procesarDatosSicoss(string $periodoFiscal): void
    {
        $config = self::getConfiguracionSicoss();

        // Obtener legajos activos
        $legajos = DB::connection(self::getMapucheConnection())->select(self::getSqlLegajos("dh01.tipo_estad = 'A'"));

        foreach ($legajos as $legajo) {
            $cuil = $legajo->nro_cuil1 . str_pad($legajo->nro_cuil, 8, '0', STR_PAD_LEFT) . $legajo->nro_cuil2;

            // Crear o actualizar registro
            self::updateOrCreate(
                [
                    'periodo_fiscal' => $periodoFiscal,
                    'cuil' => $cuil
                ],
                [
                    'apnom' => $legajo->apyno,
                    'conyuge' => $legajo->conyugue > 0,
                    'cant_hijos' => $legajo->hijos,
                    'cod_situacion' => $legajo->codigosituacion,
                    'cod_cond' => $legajo->CodigoCondicion,
                    'cod_act' => $legajo->CodigoActividad,
                    'cod_zona' => $legajo->codigozona,
                    'porc_aporte' => $legajo->aporteAdicional,
                    'cod_mod_cont' => $legajo->codigocontratacion,
                    'tipo_empresa' => $config['tipo_empresa'],
                    'regimen' => $legajo->codc_bprev === $config['codc_reparto'] ? '1' : '0',
                    'sit_rev1' => $legajo->codigosituacion,
                    'dia_ini_sit_rev1' => '01',
                    'convencionado' => $legajo->trabajadorconvencionado,
                    'informar_becarios' => $config['informar_becarios'],
                    'art_con_tope' => $config['art_con_tope'],
                    'conceptos_no_remun_art' => $config['conceptos_no_remun_art'],
                    'categorias_aportes_diferenciales' => $config['categorias_aportes_diferenciales'],
                    'hs_extras_novedades' => $config['hs_extras_novedades'],
                    'codc_reparto' => $config['codc_reparto'],
                ]
            );
        }
    }
}
