<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Database\Factories\Dh16Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Conceptos por Grupo en sistema Mapuche.
 *
 * @property int $codn_grupo Código de grupo
 * @property int $codn_conce Código de concepto
 *
 * @method static \Database\Factories\Dh16Factory factory()
 */
class Dh16 extends Model
{
    use HasFactory;
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
    protected $table = 'dh16';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'codn_grupo',
        'codn_conce',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Dh16Factory
    {
        return Dh16Factory::new();
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'codn_grupo' => 'integer',
            'codn_conce' => 'integer',
        ];
    }
}
