<?php

namespace App\Filament\Reportes\Resources\BloqueosResource\Pages;

use Filament\Forms\Form;
use Livewire\WithFileUploads;
use App\Imports\BloqueosImport;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Mapuche\Dh22Service;
use Filament\Forms\Components\Select;
use App\Traits\MapucheConnectionTrait;
use App\Services\ImportDataTableService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Services\Reportes\BloqueosService;
use App\Services\Imports\BloqueosImportService;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Imports\ImportNotificationService;
use App\Services\Imports\DuplicateValidationService;
use App\Filament\Reportes\Resources\BloqueosResource;
use App\Services\Validation\ExcelRowValidationService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;

class ImportData extends Page
{
    use MapucheConnectionTrait;
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = BloqueosResource::class;
    protected static string $view = 'filament.reportes.resources.import-data-resource.pages.import-data';
    protected static ?string $title = 'Importar Datos';

    public array $data = [];

    public function mount(): void
    {
        $tableService = new ImportDataTableService();
        $tableService->ensureTableExists();
    }

    public function getSubheading(): string
    {
        return 'Este formulario permite importar bloqueos masivos desde un archivo Excel.
                Seleccione la liquidación correspondiente y cargue el archivo con el formato establecido.';
    }

    public function form(Form $form): Form
    {
        $dh22Service = new Dh22Service();
        $liquidaciones = $dh22Service->getLiquidacionesParaSelect();

        return $form
            ->schema([
                Select::make('nro_liqui')
                    ->label('Nro. Liquidación')
                    ->options($liquidaciones)
                    ->required()
                    ->placeholder('Selecciona una liquidación'),
                FileUpload::make('excel_file')
                    ->label('Archivo Excel')
                    ->disk('public')
                    ->directory('import_bloqueos')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->maxSize(10240)
                    ->required()
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $filePath = collect($this->data['excel_file'])->first()->getRealPath();
        $nroLiqui = (int)$this->data['nro_liqui'];
        $connection = $this->getConnectionFromTrait();

        try {
            // Validación previa del archivo
            if (!file_exists($filePath)) {
                throw new \Exception('El archivo no existe o no es accesible');
            }



            Log::debug('Iniciando importación', [
                'liquidacion' => $nroLiqui,
                'archivo' => $filePath
            ]);

            // Creamos una instancia del importador con sus dependencias
            $import = new BloqueosImport(
                $nroLiqui,
                app(DuplicateValidationService::class),
                app(BloqueosServiceInterface::class, [
                    'importService' => app(BloqueosImportService::class),
                    'processService' => app(BloqueosProcessService::class),
                    'nroLiqui' => $nroLiqui
                ]),
                app(ImportNotificationService::class),
                app(ExcelRowValidationService::class),
            );

            // Importamos el archivo
            Log::debug("Importando archivo: $filePath");
            Excel::import($import, $filePath);

            // Obtenemos los resultados detallados del import
            $resultados = $import->getProcessedRowsCount();
            $importResult = $import->getImportResult();

            if ($importResult->success) {
                app(ImportNotificationService::class)->notifyImportResults(
                    $importResult->getProcessedCount(),
                    $importResult->getDuplicateCount()
                );
                Log::info('Importación completada exitosamente', $importResult->toArray());

                // Redirigir al index después de una importación exitosa
                $this->redirect(BloqueosResource::getUrl('index'));
            } else {
                throw new \Exception($importResult->message, 0, $importResult->error);
            }

            // Limpieza del archivo temporal
            if (file_exists($filePath)) {
                unlink($filePath);
            }

        } catch (\Exception $e) {


            Log::error('Error en importación', [
                'file' => $filePath,
                'liquidacion' => $nroLiqui,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            app(ImportNotificationService::class)->sendErrorNotification($e->getMessage());
        }
    }




    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
