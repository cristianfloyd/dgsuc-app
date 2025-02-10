<?php

namespace App\Models;

use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Mapuche\Dh22;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfipMapucheArt extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'afip_art';
    protected $schema = 'suc';

    protected $primaryKey = 'id';
    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'nro_legaj',
        'cuil',
        'apellido_y_nombre',
        'nacimiento',
        'sueldo', // rem_9
        'sexo',
        'establecimiento',  // codc_uacad
        'tarea',           // se mapeará desde Dh03 (campo tipo_escal)
    ];

    // Conversión de tipos de datos
    protected $casts = [
        'nacimiento' => 'date',
    ];


    /**
     * Obtiene la conexión de Mapuche.
     *
     * @return \Illuminate\Database\Connection
     */
    public static function getMapucheConnection()
    {
        return (new static)->getConnectionFromTrait();
    }

    /**
     * Método para actualizar los datos de ART desde SICOSS y Mapuche
     * Actualiza el registro obteniendo datos de:
     *  - AfipMapucheSicoss: CUIL, apellido_y_nombre y sueldo (rem_imp9)
     *  - Dh01: nacimiento (fec_nacim) y sexo (tipo_sexo),
     *  - Dh03: establecimiento (codc_uacad) y tarea (tipo_escal)
     *
     * @param string $periodoFiscal Formato esperado: YYYYMM
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function actualizarAfipArt(string $periodoFiscal): bool
    {
        // Obtener datos desde AfipMapucheSicoss usando el periodo fiscal
        $sicoss = AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $this->cuil)
            ->first();

        if ($sicoss) {
            $this->cuil = $sicoss->cuil;
            $this->apellido_y_nombre = $sicoss->apnom;
            $this->sueldo = $sicoss->rem_imp9;
        }

        // Obtener datos desde Dh01 usando el CUIL (11 dígitos)
        $dh01 = Dh01::whereRaw("CONCAT(nro_cuil1, nro_cuil, nro_cuil2) = ?", [$this->cuil])->first();
        if ($dh01) {
            $this->nacimiento = $dh01->fec_nacim;
            $this->sexo = $dh01->tipo_sexo;

            // Buscar en Dh03 mediante el número de legajo obtenido de Dh01
            $dh03 = Dh03::where('nro_legaj', $dh01->nro_legaj)->first();
            if ($dh03) {
                $this->establecimiento = $dh03->codc_uacad;
                $this->tarea = $dh03->tipo_escal;
            }
        }

        return $this->save();
    }

    /**
     * Método privado para obtener el número de legajo desde un CUIL de 11 dígitos.
     *
     * @param string $cuil
     * @return int|null El número de legajo si se encuentra, o null en caso contrario.
     */
    private function obtenerLegajoDesdeCuil(string $cuil): ?int
    {
        $dh01 = Dh01::whereRaw("CONCAT(nro_cuil1, nro_cuil, nro_cuil2) = ?", [$cuil])->first();
        return $dh01 ? $dh01->nro_legaj : null;
    }

    /**
     * Método para verificar que el número de liquidación exista en la tabla mapuche.dh21.
     *
     * @param int $nroLiqui
     * @return bool True si nroLiqui existe, false en caso contrario.
     */
    private function verificarNroLiqui($nroLiqui): bool
    {
        return Dh22::verificarNroLiqui($nroLiqui);
    }


    /**
     * Método estático para actualizar todos los registros de ART para un período fiscal
     * usando una única operación SQL.
     *
     * @param string $periodoFiscal
     * @return int Número de registros procesados
     */
    public static function actualizarAfipArtBatch(string $periodoFiscal): int
    {
        $sql = "
            INSERT INTO suc.afip_art (nro_legaj,
                          cuil,
                          apellido_y_nombre,
                          nacimiento,
                          sueldo,
                          sexo,
                          establecimiento,
                          tarea)
            WITH base_cuils AS (SELECT DISTINCT cuil, CAST( SUBSTRING( cuil, 3, LENGTH( cuil ) - 3 ) AS INTEGER ) AS dni
                                FROM suc.afip_mapuche_sicoss),
                 latest_dh03 AS (SELECT DISTINCT ON (nro_legaj) nro_legaj, codc_categ, codc_uacad, chkstopliq
                                 FROM mapuche.dh03
                                 ORDER BY nro_legaj, fec_alta DESC)
            SELECT d.nro_legaj,
                   b.cuil,
                   TRIM( s.apnom )::VARCHAR       AS apellido_y_nombre,
                   d.fec_nacim                    AS nacimento,
                   s.rem_imp9::NUMERIC(15, 2)     AS sueldo,
                   d.tipo_sexo                    AS sexo,
                   TRIM( d3.codc_uacad )::VARCHAR AS establecimiento,
                   d11.codigoescalafon            AS tarea
            FROM base_cuils b
            	     LEFT JOIN suc.afip_mapuche_sicoss s ON b.cuil = s.cuil
            	     LEFT JOIN mapuche.dh01 d ON b.dni = d.nro_cuil
            	     LEFT JOIN latest_dh03 d3 ON d.nro_legaj = d3.nro_legaj
            	     LEFT JOIN mapuche.dh11 d11 ON d3.codc_categ = d11.codc_categ
        ";

        return DB::connection(self::getMapucheConnection())
            ->affectingStatement($sql, [
                'periodo_fiscal' => $periodoFiscal
            ]);
    }

    // ########################################## RELACIONES ##########################################

    public function afip_mapuche_sicoss(): HasOne
    {
        return $this->hasOne(AfipMapucheSicoss::class, 'cuil', 'cuil');
    }

    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'establecimiento', 'desc_abrev')
            ->where('nro_tabla', 13)
            ->withoutGlobalScopes()
            ->where(function ($query) {
                if ($this->establecimiento) {
                    $query->whereRaw('TRIM("mapuche"."dh30"."desc_abrev") = TRIM(?)', [$this->establecimiento]);
                }
            });
    }

    // ########################################## ACCESORES y MUTADORES ##########################################
    protected function establecimiento(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? trim($value) : null,
            set: fn($value) => $value ? trim($value) : null
        );
    }
}
