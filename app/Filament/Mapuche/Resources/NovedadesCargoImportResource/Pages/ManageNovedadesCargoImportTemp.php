<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;
use App\Services\NovedadesCargoImportTableService;
use App\Services\NovedadesCargoImportTempService;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;

class ManageNovedadesCargoImportTemp extends ManageRecords
{
    // Campos para capturar el archivo de texto y otras opciones
    public ?string $txtFile = null;

    protected static string $resource = NovedadesCargoImportResource::class;

    public function mount(): void
    {
        parent::mount();

        // Crea la tabla temporal al entrar en la página, si no existe:
        try {
            $tempService = new NovedadesCargoImportTableService();
            $tempService->createTempTable();
        } catch (\Throwable $e) {
            $this->notify('danger', 'Error al crear tabla temporal: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Método para subir el archivo y procesarlo
    // -------------------------------------------------------------------------
    public function importFile(): void
    {
        try {
            if (!$this->txtFile) {
                $this->notify('danger', 'Por favor, selecciona un archivo antes de importar.');
                return;
            }

            // Obtenemos la ruta física del archivo que se subió
            $path = Storage::disk('local')->path($this->txtFile);

            // Instanciamos el servicio que parsea e inserta en la tabla temporal
            $tempService = new NovedadesCargoImportTempService();

            // Aquí parseas tu archivo (con substr, etc.) y en cada línea
            // vas llamando a $tempService->insertTempData($data).
            // Se muestra un ejemplo muy breve de lectura de todas las líneas:
            $handle = fopen($path, 'r');
            while (($line = fgets($handle)) !== false) {
                // supongamos que parseamos mínimamente...
                $data = [
                    'codigoNovedad' => substr($line, 0, 9),
                    // ...
                    'errors' => json_encode([]),
                ];
                $tempService->insertTempData($data);
            }
            fclose($handle);

            $this->notify('success', 'Archivo importado exitosamente.');
            // Limpias si deseas: $this->txtFile = null;
        } catch (\Throwable $e) {
            $this->notify('danger', 'Error al importar: ' . $e->getMessage());
        }
    }
}
