<?php

namespace App\Services;

use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

abstract class AbstractFileProcessor
{
    // Método para validar el archivo
    protected function validateFile(string $filePath): void
    {
        if (empty($filePath) || !Storage::exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: $filePath");
        }
    }

    // Método para leer las líneas del archivo
    protected function readFileLines(string $filePath): array
    {
        $this->validateFile($filePath);

        $extractedLines = [];
        $fileHandle = fopen($filePath, "r");

        if ($fileHandle) {
            while (($line = fgets($fileHandle)) !== false) {
                $lineUTF8 = mb_convert_encoding($line, 'UTF-8', mb_detect_encoding($line, 'UTF-8, ISO-8859-1', true));
                $extractedLines[] = $lineUTF8;
            }
            fclose($fileHandle);
        } else {
            throw new RuntimeException("Unable to open file: $filePath");
        }

        return $extractedLines;
    }

    // Método abstracto para procesar una línea
    abstract protected function processLine(string $line, array $columnWidths): array;

    // Método para procesar todo el archivo
    public function processFile(string $filePath, array $columnWidths): array
    {
        return collect($this->readFileLines($filePath))
            ->map(fn($line) => $this->processLine($line, $columnWidths))
            ->toArray();
    }
}
