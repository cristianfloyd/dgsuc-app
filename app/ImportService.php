<?php

namespace App;

use App\Models\AfipSicossDesdeMapuche;
use App\Models\UploadedFile;
use App\Services\TableManagementService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Log;

class ImportService
{
    use MapucheConnectionTrait;

    protected $tableManagementService;

    public function __construct(TableManagementService $tableManagementService)
    {
        $this->tableManagementService = $tableManagementService;
    }

    /**
     * Importa un archivo de AFIP SICOSS desde Mapuche.
     *
     * @param UploadedFile $file El archivo a importar.
     *
     * @return bool Verdadero si la importación fue exitosa, falso en caso contrario.
     */
    public function importFile(UploadedFile $file): bool
    {
        $tableName = 'suc.afip_mapuche_sicoss';
        $connection = $this->getConnectionName();
        $this->tableManagementService->verifyAndPrepareTable($tableName, $connection);

        AfipSicossDesdeMapuche::importarDesdeArchivo($file->file_path, $file->periodo_fiscal);
        Log::info("Archivo {$file->id} importado a la tabla $tableName.");
        return true;
    }
}
