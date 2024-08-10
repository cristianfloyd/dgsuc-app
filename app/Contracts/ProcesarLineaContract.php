<?php
namespace App\Contracts;

interface ProcesarLineaContract
{
    //public function prodessLine(string $line,array $columnWidths): array;
    public function processLine(string $line,array $columnWidths): array;

}

