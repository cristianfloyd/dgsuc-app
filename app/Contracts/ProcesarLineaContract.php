<?php
namespace App\Contracts;

interface ProcesarLineaContract
{
    //public function procesarlinea(string $line,array $columnWidths): array;
    public function procesar(string $line,array $columnWidths): array;

}

