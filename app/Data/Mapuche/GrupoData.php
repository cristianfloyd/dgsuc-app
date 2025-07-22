<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class GrupoData extends Data
{
    public function __construct(
        #[MapName('id_grupo')]
        public readonly int $idGrupo,
        public readonly string $nombre,
        public readonly string $tipo,
        public readonly ?string $descripcion,
        #[MapName('fec_modificacion')]
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly \DateTime $fechaModificacion,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'nombre' => ['required', 'string', 'max:30'],
            'tipo' => ['required', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string'],
        ];
    }
}
