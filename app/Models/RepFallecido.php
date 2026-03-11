<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\Reportes\FallecidoData;
use App\Services\FallecidosTableService;
use App\Traits\FilamentTableInitializationTrait;
use App\Traits\MapucheConnectionTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $nro_legaj Número de legajo
 * @property string $apellido Apellido del fallecido
 * @property string $nombre Nombre del fallecido
 * @property string $cuil CUIL del fallecido
 * @property string $codc_uacad Código de unidad académica
 * @property Carbon|null $fec_defun Fecha de defunción
 */
class RepFallecido extends Model
{
    use HasFactory;
    use FilamentTableInitializationTrait;
    use MapucheConnectionTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suc.rep_fallecidos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_legaj',
        'apellido',
        'nombre',
        'cuil',
        'codc_uacad',
        'fec_defun',
    ];

    public static function getTableServiceClass(): string
    {
        return FallecidosTableService::class;
    }

    /**
     * Transform the model to a Data object.
     */
    public function toData(): FallecidoData
    {
        return FallecidoData::from($this);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'nro_legaj' => 'integer',
            'apellido' => 'string',
            'nombre' => 'string',
            'cuil' => 'string',
            'codc_uacad' => 'string',
            'fec_defun' => 'date',
        ];
    }
}
