<?php

namespace App\Services\SicossFileProcessors;

use Illuminate\Support\Facades\Log;

class SicossFileProcessor
{
    private const CHUNK_SIZE = 1000;

    public function processFile(string $filePath, int $batchSize = 1000): \Generator
    {
        // Validación del tamaño del lote
        $batchSize = max(1, $batchSize);

        // Apertura del archivo con manejo de errores
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("No se pudo abrir el archivo: {$filePath}");
        }

        Log::info("Processing file: $filePath");

        try {
            $buffer = [];

            while (!feof($handle)) {
                $line = fgets($handle);

                // Saltear líneas vacías
                if (empty(trim($line))) {
                    continue;
                }

                $buffer[] = $line;

                // Cuando el buffer alcanza el tamaño del lote, lo enviamos
                if (\count($buffer) >= $batchSize) {
                    yield $buffer;
                    $buffer = [];
                }
            }

            // Enviar el último lote si quedaron registros en el buffer
            if (!empty($buffer)) {
                yield $buffer;
            }
        } finally {
            // Aseguramos que el archivo se cierre incluso si hay excepciones
            fclose($handle);
        }
    }
}
