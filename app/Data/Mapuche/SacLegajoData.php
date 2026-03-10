<?php

declare(strict_types=1);

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SacLegajoData extends Data
{
    public function __construct(
        public int $nro_legajo,
        public string $apellido_nombres,
        public string $documento,
        public string $tipo_documento,
        public string $dependencia,
        /** @var DataCollection<SacCargoData> */
        public DataCollection $cargos,
        /** @var DataCollection<SacCargoData> */
        public DataCollection $cargos_vigentes,
    ) {
    }
}
