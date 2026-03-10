<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use App\Models\Mapuche\Dh10;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SacCargoData extends Data
{
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
    ) {
    }

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
