<?php

namespace App\Contracts;

use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Collection;

interface FileProcessorInterface
{

    /**
     * Procesa un archivo cargado y devuelve un array con los resultados.
     *
     * @param string $filePath El archivo cargado a procesar.
     * @param array $columnWidths Las anchas de las columnas del archivo.
     * @return Collection Los resultados del procesamiento del archivo.
     */
    public function processFile(string $filePath, array $columnWidths): Collection;

    /**
     * Obtiene los detalles del archivo cargado.
     *
     * @param UploadedFile $file El archivo cargado.
     * @return array Los detalles del archivo.
     */
    public function getFileDetails(UploadedFile $file): array;

    /**
     * Importa un archivo y procesa su contenido.
     *
     * @param UploadedFile $file El archivo cargado a importar.
     * @param array $columnWidths Las anchas de las columnas del archivo.
     * @return Collection Los resultados del procesamiento del archivo.
     */
    public function handleFileImport(UploadedFile $file, string $system): Collection;
}
