<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\Pages;

use App\Filament\Bloqueos\Resources\BloqueosResource;
use App\Imports\BloqueosImport;
use App\Services\ImportDataTableService;
use App\Services\Imports\BloqueosImportService;
use App\Services\Imports\DuplicateValidationService;
use App\Services\Imports\ImportNotificationService;
use App\Services\Mapuche\Dh22Service;
use App\Services\Reportes\BloqueosImportOrchestratorService;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;
use App\Services\Validation\ExcelRowValidationService;
use App\Traits\MapucheConnectionTrait;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportData extends Page
{
    use MapucheConnectionTrait;
    use InteractsWithForms;
    use WithFileUploads;

    public array $data = [];

    protected static string $resource = BloqueosResource::class;

    protected static string $view = 'filament.reportes.resources.import-data-resource.pages.import-data';

    protected static ?string $title = 'Importar Datos';

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
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->maxSize(10240)
                    ->required(),
                Section::make('Opciones avanzadas')
                    ->description('Puedes personalizar el flujo de validación y procesamiento.')
                    ->collapsible()
                    ->schema([
                        Toggle::make('validar_todos')
                            ->label('Validar todos los registros')
                            ->offColor('warning')
                            ->onColor('success')
                            ->helperText('Si está marcado, se validarán todos los registros.')
                            ->default(true),
                        Toggle::make('validar_cargos_asociados')
                            ->label('Validar cargos asociados')
                            ->offColor('warning')
                            ->onColor('success')
                            ->helperText('Si está marcado, se validarán los cargos asociados.')
                            ->default(true),
                        Toggle::make('procesar_todo')
                            ->label('Procesar bloqueos y limpiar duplicados automáticamente después de importar')
                            ->offColor('warning')
                            ->onColor('success')
                            ->helperText('Si está marcado, se procesarán y limpiarán los registros automáticamente después de la importación.')
                            ->default(false),
                    ])
                    ->columns(3),
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
                'archivo' => $filePath,
            ]);

            // Creamos una instancia del importador con sus dependencias
            $import = new BloqueosImport(
                $nroLiqui,
                app(DuplicateValidationService::class),
                app(BloqueosServiceInterface::class, [
                    'importService' => app(BloqueosImportService::class),
                    'processService' => app(BloqueosProcessService::class),
                    'nroLiqui' => $nroLiqui,
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
                    $importResult->getDuplicateCount(),
                );
                Log::info('Importación completada exitosamente', $importResult->toArray());

                // --- INICIO: Ejecutar el orquestador ---
                $procesarTodo = !empty($this->data['procesar_todo']);
                $validarTodos = !empty($this->data['validar_todos']);
                $validarCargosAsociados = !empty($this->data['validar_cargos_asociados']);
                $orquestador = app(BloqueosImportOrchestratorService::class);
                $resultados = $orquestador->ejecutarSecuenciaCompleta($procesarTodo, $validarTodos, $validarCargosAsociados);

                if ($resultados['success']) {
                    Notification::make()
                        ->title('Importación y procesamiento completados')
                        ->body('Todos los pasos se ejecutaron correctamente.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error en el procesamiento posterior a la importación')
                        ->body('Ocurrió un error: ' . $resultados['error'])
                        ->danger()
                        ->send();
                    // decidir si redirigir o no en caso de error
                }
                // --- FIN: Ejecutar el orquestador ---

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
                'trace' => $e->getTraceAsString(),
            ]);

            app(ImportNotificationService::class)->sendErrorNotification($e->getMessage());
        }
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
