<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Registro persistido del contenido CHE generado (neto liquidado, aportes/retenciones).
 *
 * @property int $id
 * @property int $nro_liqui
 * @property string $neto_liquidado
 * @property string $accion
 * @property array $grupo_aportes_retenciones
 */
class CheRecord extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.che_data';

    protected $fillable = [
        'nro_liqui',
        'neto_liquidado',
        'accion',
        'grupo_aportes_retenciones',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'nro_liqui' => 'integer',
            'grupo_aportes_retenciones' => 'array',
        ];
    }
}
