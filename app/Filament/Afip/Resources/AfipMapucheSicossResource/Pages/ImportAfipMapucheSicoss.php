<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicossResource;
use App\Services\AfipMapucheSicossImportService;
use App\Services\Mapuche\PeriodoFiscalService;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportAfipMapucheSicoss extends Page
{
    use InteractsWithForms;

    // Propiedades para tracking del progreso
    public $importProgress = 0;

    public $totalRecords = 0;

    public ?array $data = [];

    public $file;

    public $year;

    public $month;

    public $memoryUsage = 0;

    public $processedRecords = 0;

    protected static string $resource = AfipMapucheSicossResource::class;

    protected static string $view = 'filament.resources.afip-mapuche-sicoss.pages.import';

    protected PeriodoFiscalService $periodoFiscalService;

    public function boot(): void
    {
        $this->periodoFiscalService = app(PeriodoFiscalService::class);
    }

    public function mount(): void
    {
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();

        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        $this->form->fill([
            'year' => $this->year,
            'month' => $this->month,
            'file' => null,
        ]);
    }

    public function import(): void
    {
        try {
            $data = $this->form->getState();
            $filePath = Storage::disk('public')->path($data['file']);

            if (!$this->validateFile($filePath)) {
                $this->showErrorNotification('El archivo no cumple con el formato esperado');
                return;
            }

            $service = app(AfipMapucheSicossImportService::class);
            $periodoFiscal = $data['year'] . \sprintf('%02d', $data['month']);

            // Usar streams para procesar el archivo
            $result = $service->streamImport(
                $filePath,
                $periodoFiscal,
                fn ($progress) => $this->updateImportProgress($progress),
            );

            $this->handleImportResult($result);
            $this->cleanupAfterImport($filePath);
        } catch (\Exception $e) {
            Log::error('Error en importación SICOSS', [
                'error' => $e->getMessage(),
                'file' => $data['file'] ?? 'No file specified',
            ]);

            $this->showErrorNotification('Error durante la importación: ' . $e->getMessage());
        }
    }

    protected function getFormSchema(): array
    {
        return [

            Section::make('Importación de archivo SICOSS')
                ->description('Seleccione el archivo TXT generado por Mapuche->sicoss para importar')
                ->schema([
                    Group::make([
                        Section::make('Período Fiscal')
                            ->schema([
                                Select::make('year')
                                    ->label('Año')
                                    ->options($this->getYearOptions())
                                    ->default($this->year)
                                    ->required(),
                                Select::make('month')
                                    ->label('Mes')
                                    ->options($this->getMonthOptions())
                                    ->default($this->month)
                                    ->required(),
                            ])
                            ->columnSpan(1),
                        FileUpload::make('file')
                            ->label('Archivo TXT')
                            ->acceptedFileTypes([
                                'text/plain',
                                'text/csv',
                                'application/txt',
                                'text/x-csv',
                                'application/x-txt',
                            ])
                            ->maxSize(40960) // 40MB
                            ->helperText('Formato esperado: Archivo TXT de SICOSS')
                            ->disk('public')
                            ->directory('afip-mapuche-sicoss')
                            ->visibility('private')
                            ->required()
                            ->preserveFilenames()
                            ->columnSpan(2),
                    ])
                        ->columns(3),
                ])
                ->collapsible(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('download_template')
            //     ->label('Descargar Plantilla')
            //     ->url(route('download.sicoss.template'))
            //     ->icon('heroicon-o-document-download')
        ];
    }

    protected function validateFile(string $filePath): bool
    {
        // Verificar extensión
        $extension = pathinfo($filePath, \PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'txt') {
            return false;
        }

        // Verificar que sea legible como texto
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return false;
        }

        // Leer primeras líneas para verificar formato
        $line = fgets($handle);
        fclose($handle);
        Log::info('ImportAfipMapucheSicoss::validateFile', [$line]);
        return $line !== false;
    }

    protected function updateImportProgress(array $progressData): void
    {
        // Actualizar el progreso general
        $this->importProgress = $progressData['percentage'] ?? 0;

        // Actualizar estadísticas detalladas
        $this->totalRecords = $progressData['total_records'] ?? 0;
        $this->processedRecords = $progressData['processed'] ?? 0;
        $this->memoryUsage = $progressData['memory'] ?? '0MB';

        // Emitir evento para la UI con información detallada
        $this->dispatch('import-progress-updated', [
            'progress' => $this->importProgress,
            'processed' => $this->processedRecords,
            'total' => $this->totalRecords,
            'memory' => $this->memoryUsage,
            'speed' => $progressData['records_per_second'] ?? 0,
        ]);

        // Actualizar la UI de Filament
        $this->updateProgressIndicator();
    }

    protected function handleImportResult(array $result): void
    {
        if (empty($result['errors'])) {
            Notification::make()
                ->success()
                ->title('Importación exitosa')
                ->body("Se importaron {$result['imported']} registros correctamente")
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Ver registros')
                        ->url(AfipMapucheSicossResource::getUrl('index')),
                ])
                ->persistent()
                ->send();
        } else {
            $this->showErrorNotification(
                'Importación con errores: ' . implode("\n", $result['errors']),
            );
        }
    }

    protected function showErrorNotification(string $message): void
    {
        Notification::make()
            ->danger()
            ->title('Error')
            ->body($message)
            ->persistent()
            ->send();
    }

    protected function cleanupAfterImport(string $filePath): void
    {
        // Limpieza de archivos temporales si es necesario
        Storage::delete($filePath);
    }

    private function updateProgressIndicator(): void
    {
        if ($this->importProgress >= 100) {
            Notification::make()
                ->success()
                ->title('Procesamiento completado')
                ->body("Se procesaron {$this->processedRecords} registros")
                ->persistent()
                ->send();
        } elseif ($this->importProgress > 0) {
            Notification::make()
                ->info()
                ->title('Procesando...')
                ->body("{$this->importProgress}% completado")
                ->send();
        }
    }

    /**
     * Genera las opciones de años para el selector
     * Muestra 5 años anteriores y 1 año posterior al actual.
     */
    private function getYearOptions(): array
    {
        $currentYear = Carbon::now()->year;
        return array_combine(
            range($currentYear - 5, $currentYear + 1),
            range($currentYear - 5, $currentYear + 1),
        );
    }

    /**
     * Genera las opciones de meses para el selector
     * Retorna array asociativo con número de mes => nombre del mes.
     */
    private function getMonthOptions(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }
}
