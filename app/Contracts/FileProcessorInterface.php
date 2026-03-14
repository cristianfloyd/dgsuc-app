<?php

namespace App\Contracts;

use App\Models\UploadedFile;
use Illuminate\Support\Collection;

interface FileProcessorInterface
{
    /**
     * Procesa un archivo cargado y devuelve un array con los resultados.
     *
     * @param  string  $filePath  El archivo cargado a procesar.
     * @param  array<int, int>  $columnWidths  Las anchas de las columnas del archivo.
     * @return Collection<int, Collection<int, int|string>> Los resultados del procesamiento del archivo.
     */
    public function processFile(string $filePath, array $columnWidths, ?UploadedFile $uploadedFile = null): Collection;

    /**
     * Obtiene los detalles del archivo cargado.
     *
     * @param  UploadedFile  $file  El archivo cargado.
     * @return array<string, mixed> Los detalles del archivo.
     */
    public function getFileDetails(UploadedFile $file): array;

    /**
     * Maneja la importación de un archivo.
     *
     * @param  UploadedFile  $file  El archivo cargado.
     * @param  string  $system  El sistema al que pertenece el archivo.
     * @return Collection<int, array<string, mixed>> Los resultados del procesamiento del archivo.
     */
    public function handleFileImport(UploadedFile $file, string $system): Collection;
}
