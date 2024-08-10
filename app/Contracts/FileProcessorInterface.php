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
     * @return bool Verdadero si la importación se realizó correctamente, falso en caso contrario.
     */
    public function handleFileImport(UploadedFile $file, array $columnWidths): bool;
}
