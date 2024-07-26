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

    /**
     * Importa un archivo de AFIP SICOSS desde Mapuche.
     *
     * @param UploadedFile $file El archivo a importar.
     * @return bool Verdadero si la importación fue exitosa, falso en caso contrario.
     */
    public function importFile(UploadedFile $file): bool
    {
        return AfipSicossDesdeMapuche::importarDesdeArchivo($file->file_path, $file->periodo_fiscal);
    }
}
