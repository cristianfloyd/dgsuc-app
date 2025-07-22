<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_juzgado.
 *
 * @property int $id_juzgado ID del juzgado (PK)
 * @property string $nom_juzgado Nombre del juzgado
 *
 * @method static \Database\Factories\JuzgadoFactory factory()
 */
class Juzgado extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;
    use EncodingTrait;

    public $timestamps = false;

    protected $table = 'emb_juzgado';

    protected $primaryKey = 'id_juzgado';

    /**
     * Campos que requieren conversión de codificación.
     */
    protected $encodedFields = [
        'nom_juzgado',
    ];

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nom_juzgado',
    ];

    /**
     * Casting de atributos.
     */
    protected $casts = [
        'id_juzgado' => 'integer',
        'nom_juzgado' => 'string',
    ];

    /**
     * Relación con embargos.
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_juzgado');
    }
}
