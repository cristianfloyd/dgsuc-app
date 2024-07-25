<?php

namespace App\Services;

use App\Models\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class ValidationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function validateSelectedFile(UploadedFile $archivoSeleccionado): void
    {
        if (!$archivoSeleccionado) {
            throw new InvalidArgumentException('No se ha seleccionado ningún archivo para importar.');
        }

        $filePath = storage_path("app/public/{$archivoSeleccionado->file_path}");
        if (!file_exists($filePath)) {
            throw new RuntimeException('El archivo seleccionado no existe en el sistema.');
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException('No se puede leer el archivo seleccionado.');
        }

        $allowedExtensions = ['txt', 'csv'];
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new InvalidArgumentException('El tipo de archivo seleccionado no es válido. Se esperaba un archivo .txt o .csv.');
        }
    }
}
