<?php

namespace App\Services\FileProcessors;

class SicossFileProcessor
{
    private const int CHUNK_SIZE = 1000;

    public function processFile(string $filePath): \Generator
    {
        $handle = fopen($filePath, 'r');
        $buffer = [];

        while (!feof($handle)) {
            $line = fgets($handle);
            if (empty(trim($line))) continue;

            $buffer[] = $line;

            if (count($buffer) >= self::CHUNK_SIZE) {
                yield $buffer;
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            yield $buffer;
        }

        fclose($handle);
    }
}
