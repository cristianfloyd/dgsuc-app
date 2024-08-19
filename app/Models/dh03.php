<?php

namespace App\Models;

use App\Models\SpuDisc;
use App\Models\Mapuche\Dh05;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Models\Mapuche\Catalogo\Dh36;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dh03 extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh03';
    public $timestamps = false;
    protected $primaryKey = 'nro_cargo';

    protected $fillable = [
        'nro_cargo', 'rama', 'disciplina', 'area', 'porcdedicdocente', 'porcdedicinvestig',
        'porcdedicagestion', 'porcdedicaextens', 'codigocontrato', 'horassemanales',
        'duracioncontrato', 'incisoimputacion', 'montocontrato', 'nro_legaj', 'fec_alta',
        'fec_baja', 'codc_carac', 'codc_categ', 'codc_agrup', 'tipo_norma', 'tipo_emite',
        'fec_norma', 'nro_norma', 'codc_secex', 'nro_exped', 'nro_exped_baja', 'fec_exped_baja',
        'ano_exped_baja', 'codc_secex_baja', 'ano_exped', 'fec_exped', 'nro_tab13', 'codc_uacad',
        'nro_tab18', 'codc_regio', 'codc_grado', 'vig_caano', 'vig_cames', 'chk_proye',
        'tipo_incen', 'dedi_incen', 'cic_con', 'fec_limite', 'porc_aplic', 'hs_dedic',
        'tipo_norma_baja', 'tipoemitenormabaja', 'fecha_norma_baja', 'fechanotificacion',
        'coddependesemp', 'chkfirmaencargado', 'chkfirmaautoridad', 'chkestadoafip',
        'chkestadotitulo', 'chkestadocv', 'objetocontrato', 'nro_norma_baja', 'fechagrado',
        'fechapermanencia', 'fecaltadesig', 'fecbajadesig', 'motivoaltadesig', 'motivobajadesig',
        'renovacion', 'idtareacargo', 'chktrayectoria', 'chkfuncionejec', 'chkretroactivo',
        'chkstopliq', 'nro_cargo_ant', 'transito', 'cod_clasif_cargo', 'cod_licencia',
        'cargo_concursado'
    ];

    public static function getCargoCount()
    {
        return Dh03::count();
    }



    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_cargo', 'nro_cargo');
    }
    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh11(): BelongsTo
    {
        return $this->belongsTo(Dh11::class, 'codc_categ', 'codc_categ');
    }

    public function dhc9(): BelongsTo
    {
        return $this->belongsTo(Dhc9::class, 'codc_agrup', 'codagrup');
    }


    public function dhd7(): BelongsTo
    {
        return $this->belongsTo(Dhd7::class, 'cod_clasif_cargo', 'cod_clasif_cargo');
    }


    public function spuDisc(): BelongsTo
    {
        return $this->belongsTo(SpuDisc::class, 'rama', 'rama')
                ->where('disciplina', $this->disciplina)
                ->where('area', $this->area);
    }

    public function dh05(): BelongsTo
    {
        return $this->belongsTo(Dh05::class, 'nro_licencia', 'nro_licencia');
    }

    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'codc_uacad', 'desc_abrev');
    }

    /**
     * Obtiene la dependencia asociada al cargo.
     *
     * @return BelongsTo
     */
    public function dh36(): BelongsTo
    {
        return $this->belongsTo(Dh36::class, 'coddependesemp', 'coddependesemp');
    }
}
