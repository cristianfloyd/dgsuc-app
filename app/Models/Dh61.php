<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Dh61 extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh61';

    /**
     * primaryKey compuesta en formato array
     * @var array
     */
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

    protected function codcCateg(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => preg_replace('/\s+/', '', $value),
            set: fn ($value) => preg_replace('/\s+/', '', $value)
        );
    }
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




    /**
     * Devuelve el nombre de la clave principal del modelo.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

	/**
	 * @return mixed
	 */
	public function getKey()
    {
		return implode('-', $this->primaryKey);
    }
    /**
     * Obtiene el valor de la clave única para rutas.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
    /**
     * Recupera el modelo por su clave única.
     *
     * @param  mixed  $key
     * @param  string|null  $field
     * @return Model|Collection|static[]|static|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if($field === 'id'){
            [$codc_categ, $vig_caano, $vig_cames] = explode('-', $value);
            return $this->where('codc_categ', $codc_categ)
                ->where('vig_caano', $vig_caano)
                ->where('vig_cames', $vig_cames)
                ->first();
        }
        return parent::resolveRouteBinding($value, $field);
    }

    public function newQuery()
    {
        return parent::newQuery()->addSelect(
            '*',
            DB::connection($this->getConnectionName())->raw("CONCAT(codc_categ, '-', vig_caano, '-', vig_cames) as id")
        )->orderByRaw('codc_categ, vig_caano, vig_cames');
    }
    public static function diagnosticarConexion()
    {
        $query = static::query();
        dd([
            'conexión' => $query->getConnection()->getName(),
            'sql' => $query->toSql(),
            'registros' => $query->count(),
            'primer_registro' => $query->first()
        ]);
    }
}
