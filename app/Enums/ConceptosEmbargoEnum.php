<?php

namespace App\Enums;

enum ConceptosEmbargoEnum: int
{
    /** Concepto Remunerativo */
    case REMUNERATIVO = -51;
    /** Concepto 860 */
    case CONCEPTO_860 = 860;
    /** Concepto 861 (Importe de embargo) */
    case CONCEPTO_861 = 861;
}
