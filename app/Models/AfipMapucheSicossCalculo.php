<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Mapuche\MapucheBase;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Data\AfipMapucheSicossCalculoData;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para cálculos AFIP SICOSS Mapuche
 *
 * @property string $cuil CUIL del empleado
 * @property float $remtotal Remuneración total
 * @property float $rem1 Remuneración 1
 * @property float $rem2 Remuneración 2
 * @property float $aportesijp Aportes SIJP
 * @property float $aporteinssjp Aportes INSSJP
 * @property float $contribucionsijp Contribución SIJP
 * @property float $contribucioninssjp Contribución INSSJP
 * @property float $aportediferencialsijp Aporte diferencial SIJP
 * @property float $aportesres33_41re Aportes Res. 33/41 RE
 * @property string $codc_uacad Código UA/CAD
 * @property string $caracter Caracter
 */
class AfipMapucheSicossCalculo extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Schema y tabla
     */
    protected $table = 'suc.afip_mapuche_sicoss_calculos';

    // protected $schema = 'suc';
    protected $primaryKey = 'id';

    /**
     * Sin timestamps
     */
    public $timestamps = false;

    /**
     * Campos asignables masivamente
     */
    protected $fillable = [
        'periodo_fiscal',
        'cuil',  // posicion 1 longitud 11
        'remtotal',
        'rem1',
        'rem2',
        'aportesijp', // posicion 136 longitud 15
        'aporteinssjp', // posicion 151 longitud 15
        'contribucionsijp', // posicion 301 longitud 15
        'contribucioninssjp', // posicion 316 longitud 15
        'aportediferencialsijp', // posicion 166 longitud 15
        'aportesres33_41re', // posicion 1196 longitud 15
        'codc_uacad',
        'caracter'
    ];

    /**
     * Cast de atributos
     */
    protected $casts = [
        'remtotal' => 'decimal:2',
        'rem1' => 'decimal:2',
        'rem2' => 'decimal:2',
        'aportesijp' => 'decimal:2',
        'aporteinssjp' => 'decimal:2',
        'contribucionsijp' => 'decimal:2',
        'contribucioninssjp' => 'decimal:2',
        'aportediferencialsijp' => 'decimal:2',
        'aportesres33_41re' => 'decimal:2',
        'codc_uacad' => 'string',
        'caracter' => 'string'
    ];

    /**
     * Convierte el modelo a DTO
     */
    public function toData(): AfipMapucheSicossCalculoData
    {
        return AfipMapucheSicossCalculoData::from($this);
    }
}
