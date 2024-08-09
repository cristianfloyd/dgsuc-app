<?php

namespace App\Services;

use App\Models\UploadedFile;
use App\Contracts\FileProcessorInterface;

/**
 * Procesa un archivo subido UploadedFile y devuelve un array de líneas procesadas.
 *
 * @param UploadedFile $file El archivo subido a procesar.
 * @param array $columnWidths Una matriz de anchos de columna para usar al procesar cada línea.
 * @return array Un array de líneas procesadas.
 */
class FileProcessorService implements FileProcessorInterface
{

    /** Processes an uploaded file and returns an array of processed lines.
     *
     * @param UploadedFile $file The uploaded file to process.
     * @param array $columnWidths An array of column widths to use when processing each line.
     * @return array An array of processed lines.
     */
    public function processFile(UploadedFile $file, array $columnWidths): array
    {
        $filePath = storage_path("/app/{$file->file_path}");
        $lines = collect(file($filePath));
        $periodoFiscal = $file->periodo_fiscal;



        return $lines->map(function ($line) use ($columnWidths, $periodoFiscal) {
            return $this->processLine($line, $columnWidths, $periodoFiscal);
        })->all();
    }

    /** Processes a single line of the uploaded file using the provided column widths and fiscal period.
     *
     * @param string $line The line of the file to process.
     * @param array $columnWidths An array of column widths to use when processing the line.
     * @param string $periodoFiscal The fiscal period associated with the uploaded file.
     * @return array An array of processed fields from the line.
     */
    private function processLine($line, array $columnWidths, $periodoFiscal)
    {
        $lineaProcesada = [];
        $posicion = 0;
        foreach ($columnWidths as $key => $width) {
            switch ($key) {
                case 0:
                    $lineaProcesada[] = str_replace(' ', '0', $periodoFiscal);
                    break;
                default:
                    $campo = substr($line, $posicion, $width);
                    $lineaProcesada[] = str_replace(' ', '0', $campo);
                    $posicion += $width;
                    break;
            }
        }
        return $lineaProcesada;
    }

    /**
     * Maneja la importación de un archivo.
     *
     * Este método procesa un archivo cargado utilizando los anchos de columna proporcionados.
     * Si el procesamiento se realiza correctamente, devuelve true. En caso de error, devuelve false.
     *
     * @param UploadedFile $file El archivo cargado a procesar.
     * @param array $columnWidths Un array con los anchos de columna a utilizar durante el procesamiento.
     * @return bool True si el procesamiento se realizó correctamente, false en caso de error.
     */
    public function handleFileImport(UploadedFile $file, array $columnWidths): bool
    {
        try {
            $processedLines = $this->processFile($file, $columnWidths);
            // Aquí iría la lógica para guardar o manejar las líneas procesadas
            return true;
        } catch (\Exception $e) {
            // Manejar el error, posiblemente registrándolo
            return false;
        }
    }

    /**
     * Obtiene los detalles de un archivo cargado.
     *
     * @param UploadedFile $file El archivo cargado para el que se deben obtener los detalles.
     * @return array Un array que contiene información sobre el archivo cargado, como la ruta del archivo, la ruta absoluta, el período fiscal y el nombre original del archivo.
     */
    public function getFileDetails(UploadedFile $file): array
    {
        return [
            'filepath' => $file->file_path,
            'absolutePath' => storage_path("app/{$file->file_path}"),
            'periodoFiscal' => $file->periodo_fiscal,
            'filename' => $file->original_name,
        ];
    }

}
