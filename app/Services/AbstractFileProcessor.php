<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

abstract class AbstractFileProcessor
{
    /**
     * Método para procesar todo el archivo.
     *
     * @param array<int, int> $columnWidths
     *
     * @return Collection<int, Collection<int, int|string>>
     */
    public function processFile(string $filePath, array $columnWidths): Collection
    {
        return collect($this->readFileLines($filePath))
            ->map(fn(string $line): Collection => $this->processLine($line, $columnWidths));
    }

    /**
     * Método para validar el archivo.
     */
    protected function validateFile(string $filePath): void
    {
        if ($filePath === '' || $filePath === '0' || !Storage::exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: $filePath");
        }
    }

    /**
     * Método para leer las líneas del archivo.
     *
     * @return array<int, string>
     */
    protected function readFileLines(string $filePath): array
    {
        $this->validateFile($filePath);

        $extractedLines = [];
        $fileHandle = fopen($filePath, 'r');

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

    /**
     * Método abstracto para procesar una línea.
     *
     * @param array<int, int> $columnWidths
     *
     * @return Collection<int, int|string>
     */
    abstract protected function processLine(string $line, array $columnWidths): Collection;
}
