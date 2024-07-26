<?php

namespace App;

use App\Models\AfipSicossDesdeMapuche;
use App\Models\UploadedFile;

class ImportService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function importFile(UploadedFile $file): bool
    {
        return AfipSicossDesdeMapuche::importarDesdeArchivo($file->file_path, $file->periodo_fiscal);
    }
}
