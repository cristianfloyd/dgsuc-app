<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_cuenta_judicial.
 *
 * @property string $nro_cuenta_judicial Número de cuenta judicial (PK)
 * @property int $codigo_sucursal Código de sucursal (PK)
 * @property int $nroentidadbancaria Número de entidad bancaria (PK)
 * @property string $tipo_cuenta Tipo de cuenta
 * @property int $digito_verificador Dígito verificador
 * @property string|null $cbu CBU
 * @property string $titular Titular de la cuenta
 * @property int $nrovalorpago Número valor pago
 *
 * @method static \Database\Factories\CuentaJudicialFactory factory()
 */
class CuentaJudicial extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indicar que no hay una única clave primaria autoincremental.
     */
    public $incrementing = false;

    /**
     * Desactivar timestamps de Laravel.
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'mapuche.emb_cuenta_judicial';

    /**
     * Definir clave primaria compuesta.
     */
    protected $primaryKey = ['nro_cuenta_judicial', 'codigo_sucursal', 'nroentidadbancaria'];

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nro_cuenta_judicial',
        'tipo_cuenta',
        'digito_verificador',
        'cbu',
        'titular',
        'codigo_sucursal',
        'nrovalorpago',
        'nroentidadbancaria',
    ];

    /**
     * Casting de atributos.
     */
    protected $casts = [
        'nro_cuenta_judicial' => 'string',
        'tipo_cuenta' => 'string',
        'digito_verificador' => 'integer',
        'cbu' => 'string',
        'titular' => 'string',
        'codigo_sucursal' => 'integer',
        'nrovalorpago' => 'integer',
        'nroentidadbancaria' => 'integer',
    ];

    /**
     * Obtener la clave primaria compuesta.
     */
    public function getKey()
    {
        $attributes = [];
        foreach ((array)$this->primaryKey as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }
        return $attributes;
    }

    /**
     * Relación con embargos.
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(
            Embargo::class,
            'nro_cuenta_judicial',
            'nro_cuenta_judicial',
        )->where([
            'codigo_sucursal' => $this->codigo_sucursal,
            'nroentidadbancaria' => $this->nroentidadbancaria,
        ]);
    }
}
