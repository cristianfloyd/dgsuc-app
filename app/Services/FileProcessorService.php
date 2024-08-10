<?php

namespace App\Services;

use RuntimeException;
use App\Models\UploadedFile;
use App\Services\AbstractFileProcessor;
use App\Contracts\FileProcessorInterface;

/**
 * Procesa un archivo subido UploadedFile y devuelve un array de líneas procesadas.
 *
 * @param UploadedFile $file El archivo subido a procesar.
 * @param array $columnWidths Una matriz de anchos de columna para usar al procesar cada línea.
 * @return array Un array de líneas procesadas.
 */
class FileProcessorService extends AbstractFileProcessor implements FileProcessorInterface
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
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
            // TODO: Implementar la lógica para guardar o manejar las líneas procesadas

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


    /**
     * Procesa un archivo cargado utilizando los anchos de columna proporcionados.
     *
     * Este método lee las líneas del archivo cargado, procesa cada línea utilizando los anchos de columna proporcionados y el período fiscal asociado con el archivo, y devuelve un array con las líneas procesadas.
     *
     * @param UploadedFile $file El archivo cargado a procesar.
     * @param array $columnWidths Una matriz de anchos de columna a utilizar al procesar cada línea.
     * @return array Un array de líneas procesadas.
     */
    public function processFile(UploadedFile $file, array $columnWidths): array
    {
        $filePath = storage_path("/app/{$file->file_path}");
        $lines = collect(file($filePath));
        $periodoFiscal = $file->periodo_fiscal;



        $processedLines = $lines->map(function ($line) use ($columnWidths, $periodoFiscal) {
            return $this->processLine($line, $columnWidths, $periodoFiscal);
        })->all();

        return $processedLines;
    }


    /**
     * Procesa una línea del archivo cargado utilizando los anchos de columna proporcionados y el período fiscal.
     *
     * @param string $line La línea del archivo a procesar.
     * @param array $columnWidths Un array de anchos de columna a utilizar al procesar la línea.
     * @param int $periodoFiscal El período fiscal asociado con el archivo cargado.
     * @return array $processedLines Un array de campos procesados de la línea.
     */
    private function processLine(string $line, array $columnWidths, $periodoFiscal): array
    {
        $processedLines = [];
        $posicion = 0;
        foreach ($columnWidths as $key => $width) {
            switch ($key) {
                case 0:
                    $processedLines[] = str_replace(' ', '0', $periodoFiscal);
                    break;
                default:
                    $campo = substr($line, $posicion, $width);
                    $processedLines[] = str_replace(' ', '0', $campo);
                    $posicion += $width;
                    break;
            }
        }
        return $processedLines;
    }

    /**
     * Lee y extrae las líneas de un archivo dado.
     *
     * Este método abre el archivo en modo de lectura, lee cada línea del archivo y la convierte a UTF-8 si es necesario. Las líneas extraídas se devuelven en un array.
     *
     * @param string $filePath La ruta del archivo a leer.
     * @return array Las líneas extraídas del archivo.
     * @throws RuntimeException Si el archivo no se puede leer o abrir.
     */
    public function extractLines(string $filePath): array
    {
        return $this->readFileLines($filePath);
    }
}
