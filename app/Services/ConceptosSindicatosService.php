<?php

namespace App\Services;

use App\Enums\ConceptoGrupo;

class ConceptosSindicatosService
{
    public static function getDosubaCodigos(): array
    {
        return ConceptoGrupo::DOSUBA->getConceptos();
    }

    public static function getApubaCodigos(): array
    {
        return [
            '258', //
            '266', //
            '265'  //
        ];
    }

    public static function getAdubaCodigos(): array
    {
        return [
            '270', //
            '271', //
            '273', //
        ];
    }
}
