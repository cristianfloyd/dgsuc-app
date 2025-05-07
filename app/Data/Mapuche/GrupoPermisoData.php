<?php

namespace App\Data\Mapuche;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class GrupoPermisoData extends Data
{
    public function __construct(
        #[MapName('id_grupo')]
        public readonly int $idGrupo,

        public readonly string $usuario,

        #[MapName('tipo_permiso')]
        public readonly string $tipoPermiso,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'usuario' => ['required', 'string'],
            'tipo_permiso' => ['required', 'string', 'size:1'],
        ];
    }
}
