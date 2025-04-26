<?php

namespace App\Models;

use App\Services\EncodingService;
use App\Models\Mapuche\MapucheGrupo;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dh01 extends Model
{
    use MapucheConnectionTrait, EncodingTrait;


    protected $table = 'dh01';
    public $timestamps = false;
    protected $primaryKey = 'nro_legaj';

    /**
     * Campos que requieren conversión de codificación
     */
    protected $encodedFields = [
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr'
    ];


    protected $fillable = [
        'nro_legaj',
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr',
        'nro_tabla',
        'tipo_docum',
        'nro_docum',
        'nro_cuil1',
        'nro_cuil',
        'nro_cuil2',
        'tipo_sexo',
        'fec_nacim',
        'tipo_facto',
        'tipo_rh',
        'nro_ficha',
        'tipo_estad',
        'nombrelugarnac',
        'periodoalta',
        'anioalta',
        'periodoactualizacion',
        'anioactualizacion',
        'pcia_nacim',
        'pais_nacim'
    ];

    protected $appends = [
        'cuil',
        'cuil_completo'
    ];

    // ###################################################################################
    // ######################################  RELACIONES ################################
    public function relacionesActivas(): BelongsTo
    {
        return $this->belongsTo(AfipRelacionesActivas::class, 'cuil', 'cuil');
    }

    public function dh03()
    {
        return $this->hasMany(dh03::class, 'nro_legaj', 'nro_legaj');
    }


    public function cargos()
    {
        return $this->hasMany(Dh03::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_legaj', 'nro_legaj');
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            MapucheGrupo::class,
            'mapuche.grupo_x_legajo',
            'nro_legaj',
            'id_grupo'
        );
    }

    // ####################################################################################
    // ######################################  SCOPES  ####################################

    public function scopeSearch($query, $val)
    {
        $searchTerm = EncodingService::toLatin1(strtoupper($val));

        return $query->where('nro_legaj', 'like', "%$val%")
            ->orWhere('nro_cuil', 'like', "%$val%")
            ->orWhere('desc_appat', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_apmat', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_apcas', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_nombr', 'like', '%' . strtoupper($searchTerm) . '%');
    }

    public function scopeByCuil($query, $cuil)
    {
        return $query->whereRaw("(
            LPAD(nro_cuil1::text, 2, '0') ||
            LPAD(nro_cuil::text, 8, '0') ||
            LPAD(nro_cuil2::text, 1, '0')
        ) = ?", [$cuil]);
    }

    public function getCuilCompletoAttribute()
    {
        // Aseguramos que cada parte tenga el largo correcto
        $cuil1 = str_pad($this->nro_cuil1, 2, '0', STR_PAD_LEFT);
        $cuil = str_pad($this->nro_cuil, 8, '0', STR_PAD_LEFT);
        $cuil2 = str_pad($this->nro_cuil2, 1, '0', STR_PAD_LEFT);

        return $cuil1 . $cuil . $cuil2;
    }



    // ###############################################
    // ###########  Mutadores y Accesores  ###########

    public function getDescNombrAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }

    public function setDescNombrAttribute($value)
    {
        $this->attributes['desc_nombr'] = EncodingService::toLatin1($value);
    }

    public function getCuil(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->nro_cuil1}{$this->nro_cuil}{$this->nro_cuil2}",
        );
    }

    public function getDescAppatAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }

    public function NombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->desc_appat}, {$this->desc_nombr}",
        );
    }

    protected function cuil(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Aseguramos que cada parte tenga el largo correcto
                $cuil1 = str_pad($this->nro_cuil1, 2, '0', STR_PAD_LEFT);
                $cuil = str_pad($this->nro_cuil, 8, '0', STR_PAD_LEFT);
                $cuil2 = str_pad($this->nro_cuil2, 1, '0', STR_PAD_LEFT);

                return "$cuil1$cuil$cuil2";
            }
        );
    }
}
