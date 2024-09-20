<?php

namespace App\DTOs;

class Dhe2DTO
{
    public function __construct(
        public int $nroTabla,
        public string $descAbrev,
        public ?int $codOrganismo
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nroTabla: $data['nro_tabla'],
            descAbrev: $data['desc_abrev'],
            codOrganismo: $data['cod_organismo'] ?? null
        );
    }
}
