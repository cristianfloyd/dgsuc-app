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

        #[MapName('c305')]
        public readonly float $c305,

        #[MapName('c306')]
        public readonly float $c306,
    ) {}

    /**
     * Crea una instancia de SicossReporteData a partir de un modelo.
     * 
     * @param mixed $model El modelo desde el cual crear la instancia
     * @return self Nueva instancia de SicossReporteData
     */
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
            c305: $model->c305 ?? 0, // Valor por defecto en caso de null
            c306: $model->c306 ?? 0,
        );
    }
}
