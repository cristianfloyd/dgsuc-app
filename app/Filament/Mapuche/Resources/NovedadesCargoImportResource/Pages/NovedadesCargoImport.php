<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\ListRecords;
use App\Services\NovedadesCargoImportService;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;

class NovedadesCargoImport extends ListRecords
{
    // -------------------------------------------------------------------------
    // Clase Resource a la que está asociada esta Page
    // -------------------------------------------------------------------------
    protected static string $resource = NovedadesCargoImportResource::class;

    // -------------------------------------------------------------------------
    // Datos temporales antes de procesar
    // -------------------------------------------------------------------------
    public ?string $txtFile = null;
    public bool $conActualizacion = false;
    public bool $nuevosIdentificadores = false;

    // -------------------------------------------------------------------------
    // Formulario inicial para la importación
    // -------------------------------------------------------------------------
    protected function getFormModel(): string
    {
        return static::$resource::getModel();
    }

    // -------------------------------------------------------------------------
    // Método que se invocará para realizar la importación tras enviar el form
    // -------------------------------------------------------------------------
    public function importFile()
    {
        try {
            // Verifica que haya un archivo seleccionado
            if (! $this->txtFile) {
                $this->notify('danger', 'No se seleccionó archivo para importar.');
                return;
            }

            // Obtiene la ruta de almacenamiento
            $path = Storage::disk('local')->path($this->txtFile);

            // Llama al servicio que realiza el parseo y validaciones
            $service = new NovedadesCargoImportService();
            $service->processFile($path, [
                'conActualizacion'    => $this->conActualizacion,
                'nuevosIdentificadores' => $this->nuevosIdentificadores,
            ]);

            $this->notify('success', 'Archivo importado con éxito. Revisa la tabla para ver validaciones.');

        } catch (\Throwable $th) {
            // Manejo de errores genéricos
            $this->notify('danger', 'Error al importar: ' . $th->getMessage());
        }
    }
}
