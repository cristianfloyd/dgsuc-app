<?php

namespace App\Contracts;

interface ImportacionCrudaContract
{
    public function importarArchivo(string $filePath);
}
