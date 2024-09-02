<?php

namespace App\DTOs;

class AfipMapucheSicossDTO
{
    public function __construct(
        public string $periodoFiscal,
        public string $cuil,
        public ?string $apnom = null,
        public ?string $conyuge = null,
        public ?string $cantHijos = null,
        // ... otros campos ...
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
