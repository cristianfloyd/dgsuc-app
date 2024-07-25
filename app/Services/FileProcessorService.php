<?php

namespace App\Services;

use App\Models\UploadedFile;

/**
 * Procesa un archivo subido UploadedFile y devuelve un array de líneas procesadas.
 *
 * @param UploadedFile $file El archivo subido a procesar.
 * @param array $columnWidths Una matriz de anchos de columna para usar al procesar cada línea.
 * @return array Un array de líneas procesadas.
 */
class FileProcessorService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Processes an uploaded file and returns an array of processed lines.
     *
     * @param UploadedFile $file The uploaded file to process.
     * @param array $columnWidths An array of column widths to use when processing each line.
     * @return array An array of processed lines.
     */
    public function processFile(UploadedFile $file, array $columnWidths)
    {
        $filePath = storage_path("/app/public/{$file->file_path}");
        $lines = collect(file($filePath));
        $periodoFiscal = $file->periodo_fiscal;



        return $lines->map(function ($line) use ($columnWidths, $periodoFiscal) {
            return $this->processLine($line, $columnWidths, $periodoFiscal);
        })->all();
    }

    /**
     * Processes a single line of the uploaded file using the provided column widths and fiscal period.
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

}
