<?php

namespace App\Contracts;

use App\Models\UploadedFile;

interface FileProcessorInterface
{
    /**
     * Procesa un archivo cargado y devuelve un array con los resultados.
     *
     * @param UploadedFile $file El archivo cargado a procesar.
     * @param array $columnWidths Las anchas de las columnas del archivo.
     * @return array Los resultados del procesamiento del archivo.
     */
    public function processFile(UploadedFile $file, array $columnWidths): array;
}
