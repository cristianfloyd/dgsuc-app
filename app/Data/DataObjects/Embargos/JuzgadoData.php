<?php

namespace App\Data\DataObjects\Embargos;

use Spatie\LaravelData\Data;

class JuzgadoData extends Data
{
    public function __construct(
        public readonly string $nom_juzgado,
        public readonly ?int $id_juzgado = null,
    ) {
    }
}
