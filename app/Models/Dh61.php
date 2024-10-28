<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dh61 extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh61';
    //protected $primaryKey = 'codc_categ';
    protected $primaryKey = ['codc_categ', 'vig_caano', 'vig_cames'];
    protected $fillable = ['codc_categ', 'equivalencia', 'tipo_escal', 'nro_escal', 'impp_basic', 'codc_dedic', 'sino_mensu', 'sino_djpat', 'vig_caano', 'vig_cames', 'desc_categ', 'sino_jefat', 'impp_asign', 'computaantig', 'controlcargos', 'controlhoras', 'controlpuntos', 'controlpresup', 'horasmenanual', 'cantpuntos', 'estadolaboral', 'nivel', 'tipocargo', 'remunbonif', 'noremunbonif', 'remunnobonif', 'noremunnobonif', 'otrasrem', 'dto1610', 'reflaboral', 'refadm95', 'critico', 'jefatura', 'gastosrepre', 'codigoescalafon', 'noinformasipuver', 'noinformasirhu', 'imppnooblig', 'aportalao'];

    protected $casts = [
        'impp_basic' => 'decimal:2',
        'impp_asign' => 'decimal:2',
        'controlcargos' => 'boolean',
        'remunbonif' => 'float',
        'noremunbonif' => 'float',
        'remunnobonif' => 'float',
        'noremunnobonif' => 'float',
        'otrasrem' => 'float',
        'dto1610' => 'float',
        'reflaboral' => 'float',
        'refadm95' => 'float',
        'critico' => 'float',
        'jefatura' => 'float',
        'gastosrepre' => 'float',
        'computaantig' => 'integer',
        'controlhoras' => 'integer',
        'controlpuntos' => 'integer',
        'controlpresup' => 'integer',
        'cantpuntos' => 'integer',
        'noinformasipuver' => 'integer',
        'noinformasirhu' => 'integer',
        'imppnooblig' => 'integer',
        'aportalao' => 'integer'
    ];

    // Scope para filtrar por categoría
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('codc_categ', $categoria);
    }

    // Scope para filtrar por vigencia
    public function scopeVigencia($query, $ano, $mes)
    {
        return $query->where('vig_caano', $ano)
                    ->where('vig_cames', $mes);
    }

    // Método para obtener categorías activas
    public function scopeActivas($query)
    {
        return $query->where('estadolaboral', 'A');
    }

    // Método para verificar si es jefatura
    public function esJefatura(): bool
    {
        return $this->sino_jefat === 'S';
    }

    // Método para verificar si está mensualizado
    public function esMensualizado(): bool
    {
        return $this->sino_mensu === 'S';
    }

    // Método para obtener el total de remuneraciones
    public function getTotalRemuneraciones(): float
    {
        return (float)$this->impp_basic +
               (float)$this->remunbonif +
               (float)$this->noremunbonif +
               (float)$this->remunnobonif +
               (float)$this->noremunnobonif +
               (float)$this->otrasrem;
    }

    // Método para verificar si requiere declaración jurada patrimonial
    public function requiereDeclaracionJurada(): bool
    {
        return $this->sino_djpat === 'S';
    }

    // Método para obtener categorías por escalafón
    public function scopePorEscalafon($query, $codigoEscalafon)
    {
        return $query->where('codigoescalafon', $codigoEscalafon);
    }
}
