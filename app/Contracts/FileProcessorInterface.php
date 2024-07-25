<?php

namespace App\Contracts;

use App\Models\UploadedFile;

interface FileProcessorInterface
{
    public function processFile(UploadedFile $file, array $columnWidths): array;
}
