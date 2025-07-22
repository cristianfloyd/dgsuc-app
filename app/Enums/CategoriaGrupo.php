<?php

namespace App\Enums;

enum CategoriaGrupo: string
{
    case DOCS = 'DOCS';

    case DOC2 = 'DOC2';

    case DOCU = 'DOCU';

    case AUTS = 'AUTS';

    case AUTU = 'AUTU';

    case NODO = 'NODO';

    public function description(): string
    {
        return match($this) {
            self::DOCS => 'Categorías docentes secundarios',
            self::DOC2 => 'Categorías preuniversitarios',
            self::DOCU => 'Categorías docentes universitarias',
            self::AUTS => 'Categorías autoridades secundarias',
            self::AUTU => 'Categorías autoridades universitarias',
            self::NODO => 'Categorías no docentes'
        };
    }
}
