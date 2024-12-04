<?php

namespace App\Models;

use App\Services\EncodingService;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


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
        'nro_legaj', 'desc_appat', 'desc_apmat', 'desc_apcas', 'desc_nombr', 'nro_tabla',
        'tipo_docum', 'nro_docum',
        'nro_cuil1', 'nro_cuil', 'nro_cuil2',
        'tipo_sexo', 'fec_nacim', 'tipo_facto', 'tipo_rh', 'nro_ficha', 'tipo_estad', 'nombrelugarnac',
        'periodoalta', 'anioalta', 'periodoactualizacion', 'anioactualizacion', 'pcia_nacim', 'pais_nacim'
    ];

    // Definir las relaciones
    public function dh03()
    {
        return $this->hasMany(dh03::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Obtiene los cargos asociados a este registro de Dh01.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cargos()
    {
        return $this->hasMany(Dh03::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_legaj', 'nro_legaj');
    }

    public function scopeSearch($query, $val)
    {
        $searchTerm = EncodingService::toLatin1(strtoupper($val));

        return $query->where('nro_legaj', 'like', "%$val%")
            ->orWhere('nro_cuil', 'like', "%$val%")
            ->orWhere('desc_appat', 'like', '%'.strtoupper($searchTerm).'%')
            ->orWhere('desc_apmat', 'like', '%'.strtoupper($searchTerm).'%')
            ->orWhere('desc_apcas', 'like', '%'.strtoupper($searchTerm).'%')
            ->orWhere('desc_nombr', 'like', '%'.strtoupper($searchTerm).'%');
    }

    public function getCuilCompletoAttribute()
    {
        return "{$this->nro_cuil1}{$this->nro_cuil}{$this->nro_cuil2}";
    }



    // Mutador para convertir desc_nombr a UTF-8 al obtener el valor
    public function getDescNombrAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }
    // Mutador para convertir desc_nombr a Latin1 antes de guardar
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
}


