<?php

namespace App\Models;

use App\Contracts\CategoryUpdateServiceInterface;
use App\Models\Dh61;
use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dh11 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'dh11';
    public $timestamps = false;
    protected $primaryKey = 'codc_categ';
    public $incrementing = false;
    protected $keyType = 'string';


    /**
     * Constantes agrupadas para las categorías de cargos.
     * Estos valores corresponden a la columna codc_categ del modelo Dh11.
     */
    public const array CATEGORIAS = [
        'DOCS' => [
            'HOME',
            'PEO6',
            'ACPN',
            'AYCP',
            'AYEO',
            'ATTP',
            'JTPM',
            'PR15',
            'JTEP',
            'JGEP',
            'MEPS',
            'MEPR',
            'MC20',
            'MENI',
            'MEPI',
            'MJMA',
            'PREO',
            'ASPE',
            'BIPH',
            'BIBL',
            'JBIB',
            'JEPR',
            'MCOR',
            'PR25',
            'REG1',
            'BI30',
            'HOLU',
        ],
        'DOCU' => [
            'A1EH',
            'AY1E',
            'JTEH',
            'JTPE',
            'ADEH',
            'ASEH',
            'TIEH',
            'ADJE',
            'ASOE',
            'TITE',
            'TIAE',
            'A1PH',
            'A2PH',
            'AY1P',
            'AY2P',
            'JTPH',
            'JTPP',
            'ADPH',
            'ASPH',
            'TIPH',
            'ADJP',
            'ASOP',
            'TITP',
            'TIAP',
            'A1SH',
            'AY1S',
            'JTSH',
            'JTPS',
            'ADSH',
            'ASSH',
            'TISH',
            'ADJS',
            'ASOS',
            'TITS',
            'TIAS',
            'HOCO',
            'HODI',
            'HOJE',
            'HOSU',
        ],
        'AUTS' => [
            'VD20',
            'PRSE',
            'RESE',
            'SECR',
            'SREH',
            'SREG',
            'SRE1',
            'SRG3',
            'VRSE',
            'VD30',
            'VD35',
            'DI40',
        ],
        'AUTU' => [
            'DECC',
            'SEFC',
            'SEUC',
            'VICC',
            'VIDC',
            'VIRC',
            'DECE',
            'RECT',
            'SUHE',
            'SEFE',
            'SEUE',
            'SSUN',
            'VIDE',
            'VIRE',
            'DECP',
            'SFHP',
            'SEFP',
            'SEUP',
            'VDPH',
            'VIPH',
            'VIDP',
            'VIRP',
        ],
        'NODO' => [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            'DOCA',
            'DOCC',
            'DOCE',
            'ESTI',
            'INVE',
            'MAEA',
            'MAEE',
            'MAEO',
        ],
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

    // Relación con el modelo Dh61
    public function dh61(): HasMany
    {
        return $this->hasMany(Dh61::class,'codc_categ','codc_categ')
            ->where('dh61.vig_caano', '=', $this->vig_caano)
            ->where('dh61.vig_cames', '=', $this->vig_cames);
    }

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

    public static function getCargosDoceSecundario(): int
    {
        return Cache::remember('cargos_doce_secundario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['DOCS'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    public static function getCargosDoceUniversitario(): int
    {
        return Cache::remember('cargos_doce_universitario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['DOCU'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }
    public static function getCargosAutoUniversitario(): int
    {
        return Cache::remember('cargos_auto_universitario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['AUTU'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    public static function getCargosAutoSecundario(): int
    {
        return Cache::remember('cargos_auto_secundario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['AUTS'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    public static function getCargosNoDocente(): int
    {
        return Cache::remember('cargos_no_docente', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['NODO'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Obtiene los códigos de categoría para un tipo específico.
     *
     * @param string $tipo
     * @return array
     */
    public static function getCategoriasPorTipo(string $tipo): array
    {
        return match ($tipo) {
            'DOCE' => array_merge(self::CATEGORIAS['DOCU'], self::CATEGORIAS['DOCS']),
            'AUTO' => array_merge(self::CATEGORIAS['AUTU'], self::CATEGORIAS['AUTS']),
            default => self::CATEGORIAS[$tipo] ?? [],
        };
        // return self::CATEGORIAS[$tipo] ?? [];
    }

    /**
     * Actualiza el campo impp_basic del modelo actual aplicando un porcentaje de incremento.
     *
     * @param float $porcentaje El porcentaje de incremento a aplicar.
     * @return bool Verdadero si la actualización se realizó correctamente, falso en caso contrario.
     */
    public function actualizarImppBasicPorPorcentaje(float $porcentaje): bool
    {
        $service = app(CategoryUpdateServiceInterface::class);
        return $service->updateCategoryWithHistory($this, $porcentaje);
    }
}
