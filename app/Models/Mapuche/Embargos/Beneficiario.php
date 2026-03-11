<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * Modelo Eloquent para la tabla mapuche.emb_beneficiario.
 *
 * @property string $cuit CUIT del beneficiario (PK)
 * @property string $nom_beneficiario Nombre del beneficiario
 */
class Beneficiario extends Model
{
    use HasFactory;

    /**
     * Desactivar incremento automático.
     */
    public $incrementing = false;

    /**
     * Desactivar timestamps de Laravel.
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'mapuche.emb_beneficiario';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'cuit';

    /**
     * Tipo de clave primaria.
     */
    protected $keyType = 'string';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'cuit',
        'nom_beneficiario',
    ];

    /**
     * Relación con embargos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Embargo, $this>
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'cuit', 'cuit');
    }

    /**
     * Casting de atributos.
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'cuit' => 'string',
            'nom_beneficiario' => 'string',
        ];
    }
}
