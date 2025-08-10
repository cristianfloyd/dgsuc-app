<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
* (D) Ganancias: Acum. de Empleados
*/
class Dh41 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indica si la clave primaria es autoincremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indica que el modelo no usa timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'mapuche.dh41';

    /**
     * Tipo de la clave primaria.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'nro_legaj';

    /**
     * Define las claves primarias compuestas.
     *
     * @var array
     */
    protected $compositePrimaryKey = ['nro_legaj', 'per_anoga', 'per_mesga'];

    /**
     * Atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nro_legaj',
        'per_anoga',
        'per_mesga',
        'imp_bruto',
        'imp_bruoa',
        'imp_aguin',
        'imp_aguoa',
        'imp_jubun',
        'imp_juboa',
        'imp_leyun',
        'imp_leyoa',
        'imp_obsun',
        'imp_obsoa',
        'imp_cmaun',
        'imp_cmaoa',
        'imp_cmaoa_acum',
        'imp_segun',
        'imp_segoa',
        'imp_prise_acum',
        'imp_sepun',
        'imp_sepoa',
        'imp_gtose_acum',
        'imp_segretiro_univ',
        'imp_segretiro_otact',
        'imp_segretiro_acum',
        'imp_gtomedico_univ',
        'imp_gtomedico_otact',
        'imp_gtomedico_acum',
        'imp_inthipote_univ',
        'imp_inthipote_otact',
        'imp_inthipote_acum',
        'imp_movun',
        'imp_movoa',
        'imp_sinun',
        'imp_sinoa',
        'imp_dedun',
        'imp_dedoa',
        'imp_gacum',
        'imp_donac',
        'imp_donac_otact',
        'cant_espos',
        'cant_hijos',
        'cant_otros',
        'imp_moret',
        'imp_retex',
        'imp_suret',
        'gcianetabasecalcor',
        'gcianetabaseulcalc',
        'nro_liqui',
        'montosdiferidos_1',
        'montosdiferidos_2',
        'montosdiferidos_3',
        'montosdiferidos_4',
        'montosdiferidos_5',
        'montosdiferidos_6',
        'montosdiferidos_7',
        'montosdiferidos_8',
        'montosdiferidos_9',
        'montosdiferidos_10',
        'montosdiferidos_11',
        'imp_repro',
        'imp_no_alcanzado',
        'imp_exento',
        'imp_obligatorios_ley',
        'imp_sociedades',
        'imp_cajas',
        'imp_alquileres_otact',
        'imp_alquileres_acum',
        'imp_hs_extras_otact',
        'imp_hs_extras_acum',
        'imp_exento_hs_extras',
        'imp_material_didactico',
        'imp_material_didactico_otact',
        'imp_material_didactico_acum',
        'imp_viaticos',
        'imp_viaticos_acum',
        'imp_material_didactico_real_inst',
        'imp_viaticos_real_inst',
        'imp_viaticos_otact',
        'imp_sociedades_real_inst',
        'imp_sociedades_otact',
        'imp_sociedades_acum',
        'imp_cajas_otact',
        'imp_cajas_acum',
        'imp_prise_real_inst',
        'imp_sep_real_inst',
        'imp_segretiro_real_inst',
        'imp_cma_real_inst',
        'imp_gtomedico_real_inst',
        'imp_donac_real_inst',
        'imp_inthipote_real_inst',
        'imp_donac_acum',
        'imp_no_habituales_inst',
        'imp_indumentaria',
        'imp_indumentaria_acum',
        'imp_segmixto',
        'imp_segmixto_real_inst',
        'imp_segmixto_otact',
        'imp_segretiro_privado_otact',
        'imp_fondoscomunes_retiro_otact',
        'imp_ajuste',
        'imp_exento_ajuste',
        'imp_exento_no_habituales',
        'imp_mov_real_inst',
        'imp_mov_acum',
        'imp_bonos_produc',
        'imp_bonos_produc_otact',
        'imp_bonos_produc_acum',
        'imp_bonos_produc_real_inst',
        'imp_fallo_caja',
        'imp_fallo_caja_otact',
        'imp_fallo_caja_acum',
        'imp_fallo_caja_real_inst',
        'imp_simil_naturaleza',
        'imp_simil_naturaleza_otact',
        'imp_simil_naturaleza_acum',
        'imp_simil_naturaleza_real_inst',
        'imp_teletrabajo_exento',
        'imp_suplementos_militares_exentos',
        'cant_hijos_incap',
        'imp_jubun_sac',
        'imp_leyun_sac',
        'imp_obsun_sac',
        'imp_sinun_sac',
        'imp_gtoeduc_inst',
        'imp_gtoeduc_otact',
        'imp_gtoeduc_acum',
        'imp_locatario',
        'imp_locador',
    ];

    /**
     * Sobreescribe el método para obtener la clave primaria
     * para manejar claves primarias compuestas.
     *
     * @return array
     */
    public function getKeyName()
    {
        return $this->compositePrimaryKey;
    }

    /**
     * Relación con la tabla dh22.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dh22()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
}
