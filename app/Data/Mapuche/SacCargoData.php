<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use App\Models\Mapuche\Dh10;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

/**
 * Class SacCargoData
 *
 * Representa los datos de un cargo en el sistema Mapuche.
 *
 * @property int $nro_cargo Número identificador del cargo.
 * @property int $vcl_cargo Código de vinculación del cargo.
 * @property array $importes_brutos Importes brutos mensuales por cargo.
 * @property array $importes_acumulados Importes acumulados mensuales por cargo.
 * @property array $vinculos Vinculaciones mensuales del cargo.
 * @property int $primer_semestre Año del primer semestre.
 * @property int $segundo_semestre Año del segundo semestre.
 * @property string|null $categoria Categoría del cargo.
 * @property Carbon|null $fecha_alta Fecha de alta del cargo.
 * @property Carbon|null $fecha_baja Fecha de baja del cargo.
 */
class SacCargoData extends Data
{
    /**
     * Constructor de SacCargoData
     */
    public function __construct(
        public int $nro_cargo,
        public int $vcl_cargo,
        public array $importes_brutos,
        public array $importes_acumulados,
        public array $vinculos,
        public int $primer_semestre,
        public int $segundo_semestre,
        public ?string $categoria = null,
        public ?Carbon $fecha_alta = null,
        public ?Carbon $fecha_baja = null,
    ) {}

    /**
     * Crea una nueva instancia de SacCargoData a partir de un modelo Dh10.
     */
    public static function fromModel(Dh10 $model): self
    {
        return new self(
            nro_cargo: $model->nro_cargo,
            vcl_cargo: $model->vcl_cargo,
            importes_brutos: $model->importes_brutos_mensuales,
            importes_acumulados: array_fill(1, 12, 0),
            vinculos: array_fill(1, 12, ''),
            primer_semestre: now()->year,
            segundo_semestre: now()->year,
        );
    }
}
