<?php

namespace App\Data\Responses;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class SicossReporteData extends Data
{
    public function __construct(
        #[MapName('nro_liqui')]
        public readonly int $numeroLiquidacion,

        #[MapName('desc_liqui')]
        public readonly string $descripcionLiquidacion,

        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly float $remunerativo,

        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly float $noRemunerativo,

        #[MapName('aportesijpdh21')]
        public readonly float $aportesSijp,

        #[MapName('aporteinssjpdh21')]
        public readonly float $aportesInssjp,

        #[MapName('contribucionsijpdh21')]
        public readonly float $contribucionesSijp,

        #[MapName('contribucioninssjpdh21')]
        public readonly float $contribucionesInssjp,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            numeroLiquidacion: $model->nro_liqui,
            descripcionLiquidacion: $model->desc_liqui,
            remunerativo: $model->remunerativo,
            noRemunerativo: $model->no_remunerativo,
            aportesSijp: $model->aportesijpdh21,
            aportesInssjp: $model->aporteinssjpdh21,
            contribucionesSijp: $model->contribucionsijpdh21,
            contribucionesInssjp: $model->contribucioninssjpdh21,
        );
    }
}
