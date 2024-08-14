<?php

namespace App\Enums;

enum TipoConce: string
{
    case DEBITO = 'D';
    case CREDITO = 'C';
    case O = 'O';
    case S = 'S';
    case A = 'A';
    case ESPECIAL = 'E';
}
