<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;

class GrupoLegajoData extends Data
{
    public function __construct(
        #[MapName('id_grupo')]
        public readonly int $idGrupo,

        #[MapName('nro_legaj')]
        public readonly int $nroLegajo,
    ) {}

    public static function rules(): array
    {
        return [
            'id_grupo' => ['required', 'integer', 'exists:mapuche.grupo,id_grupo'],
            'nro_legaj' => ['required', 'integer', 'exists:mapuche.legajo,nro_legaj'],
        ];
    }
}
