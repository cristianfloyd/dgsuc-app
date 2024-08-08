<?php

namespace App\Services;

use App\Models\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class ValidationService
{
    /**
     * Valida el archivo seleccionado para la importación.
     *
     * Esta función verifica que se haya seleccionado un archivo, que el archivo exista en el sistema y que sea legible. También verifica que el tipo de archivo sea válido (txt o csv).
     *
     * @param UploadedFile $archivoSeleccionado El archivo seleccionado para importar.
     * @throws InvalidArgumentException Si no se ha seleccionado ningún archivo o el tipo de archivo no es válido.
     * @throws RuntimeException Si el archivo seleccionado no existe o no se puede leer.
     */
    public function validateSelectedFile(UploadedFile $archivoSeleccionado): void
    {
        if (!$archivoSeleccionado) {
            throw new InvalidArgumentException('No se ha seleccionado ningún archivo para importar.');
        }

        $filePath = storage_path("app/{$archivoSeleccionado->file_path}");
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
