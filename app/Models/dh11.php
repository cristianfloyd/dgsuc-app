<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dh11 extends Model
{
    use MapucheConnectionTrait;


    protected $table = 'dh11';
    public $timestamps = false;
    protected $primaryKey = 'codc_categ';
    public $incrementing = false;
    protected $keyType = 'string';
    protected static array $secundario = [
        'SE25',
        'SE20',
        'SE16',
        'SE12',
        'SE30',
        'SE06',
        'SE40',
        'SE15',
        'SE35',
        'SE01',
    ];
    protected static array $universitario = [
        'EXCL',
        'SEMI',
        'PARC',
        'COMP',
    ];

    protected $fillable = [
        'codc_categ',
        'equivalencia',
        'tipo_escal',
        'nro_escal',
        'impp_basic',
        'codc_dedic',
        'sino_mensu',
        'sino_djpat',
        'vig_caano',
        'vig_cames',
        'desc_categ',
        'sino_jefat',
        'impp_asign',
        'computaantig',
        'controlcargos',
        'controlhoras',
        'controlpuntos',
        'controlpresup',
        'horasmenanual',
        'cantpuntos',
        'estadolaboral',
        'nivel',
        'tipocargo',
        'remunbonif',
        'noremunbonif',
        'remunnobonif',
        'noremunnobonif',
        'otrasrem',
        'dto1610',
        'reflaboral',
        'refadm95',
        'critico',
        'jefatura',
        'gastosrepre',
        'codigoescalafon',
        'noinformasipuver',
        'noinformasirhu',
        'imppnooblig',
        'aportalao',
        'factor_hs_catedra'
    ];
    protected $casts = [
        'controlcargos' => 'boolean',
        'controlhoras' => 'boolean',
        'controlpuntos' => 'boolean',
        'controlpresup' => 'boolean',
        'aportalao' => 'boolean',
        'remunbonif' => 'double',
        'noremunbonif' => 'double',
        'remunnobonif' => 'double',
        'noremunnobonif' => 'double',
        'otrasrem' => 'double',
        'dto1610' => 'double',
        'reflaboral' => 'double',
        'refadm95' => 'double',
        'critico' => 'double',
        'jefatura' => 'double',
        'gastosrepre' => 'double',
        'factor_hs_catedra' => 'double',
    ];
    public function dh31()
    {
        return $this->belongsTo(dh31::class, 'codc_dedic', 'codc_dedic');
    }
    public function dh89()
    {
        return $this->belongsTo(dh89::class, 'codigoescalafon', 'codigoescalafon');
    }
    public function dh03()
    {
        return $this->hasMany(dh03::class, 'codc_categ', 'codc_categ');
    }

    public static function getCargosSecundario(): int
    {
        return Cache::remember('cargos_secundario', 3600, function () {
            return self::whereIn('dh11.codc_dedic', self::$secundario)
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    public static function getCargosUniversitario(): int
    {
        return Cache::remember('cargos_universitario', 3600, function () {
            return self::whereIn('dh11.codc_dedic', self::$universitario)
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }
}
