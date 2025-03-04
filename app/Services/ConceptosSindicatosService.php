<?php

namespace App\Services;

use App\Enums\ConceptoGrupo;

class ConceptosSindicatosService
{
    public static function getDosubaCodigos(): array
    {
        return ConceptoGrupo::DOSUBASIN310->getConceptos();
    }

    public static function getApubaCodigos(): array
    {
        return [
            '258', //
            '266', //
        ];
    }

    public static function getAdubaCodigos(): array
    {
        return [
            '270', //
            '271', //
        ];
    }
}
