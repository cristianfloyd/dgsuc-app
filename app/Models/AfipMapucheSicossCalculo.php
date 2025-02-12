<?php

declare(strict_types=1);

namespace App\Models;

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
    use HasFactory, MapucheConnectionTrait;

    /**
     * Schema y tabla
     */
    protected $table = 'afip_mapuche_sicoss_calculos';
    protected $schema = 'suc';
    protected $primaryKey = 'id';

    /**
     * Sin timestamps
     */
    public $timestamps = false;

    /**
     * Campos asignables masivamente
     */
    protected $fillable = [
        'cuil',
        'remtotal',
        'rem1',
        'rem2',
        'aportesijp',
        'aporteinssjp',
        'contribucionsijp',
        'contribucioninssjp',
        'aportediferencialsijp',
        'aportesres33_41re',
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
