<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages;

use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Services\AfipMapucheSicossCalculoImportService;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource;

class ImportAfipMapucheSicossCalculo extends Page
{
    use InteractsWithForms;

    protected static string $resource = AfipMapucheSicossCalculoResource::class;
    protected static string $view = 'filament.resources.afip-mapuche-sicoss-calculo.pages.import';

    protected PeriodoFiscalService $periodoFiscalService;
    public $importProgress = 0;
    public $totalRecords = 0;
    public ?array $data = [];
    public $file = null;
    public $year = null;
    public $month = null;
    public $memoryUsage = 0;
    public $processedRecords = 0;

    public function mount(): void
    {
        $this->periodoFiscalService = app(PeriodoFiscalService::class);
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();

        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        $this->form->fill([
            'year' => $this->year,
            'month' => $this->month,
            'file' => null
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Importación de archivo SICOSS Cálculo')
                ->description('Seleccione el archivo TXT para importar')
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
                                'application/x-txt'
                            ])
                            ->maxSize(40960)
                            ->disk('public')
                            ->helperText('Formato esperado: Archivo TXT de SICOSS Cálculo')
                            ->directory('afip-mapuche-sicoss-calculo')
                            ->visibility('private')
                            ->required()
                            ->preserveFilenames()
                            ->columnSpan(2),
                    ])->columns(3)
                ])
        ];
    }

    public function import()
    {
        try {
            $data = $this->form->getState();
            $filePath = Storage::disk('public')->path($data['file']);

            $service = app(AfipMapucheSicossCalculoImportService::class);
            $periodoFiscal = $data['year'] . sprintf('%02d', $data['month']);

            $result = $service->streamImport(
                $filePath,
                $periodoFiscal,
                fn($progress) => $this->updateImportProgress($progress)
            );

            $this->handleImportResult($result);
            Storage::delete($filePath);

        } catch (\Exception $e) {
            Log::error('Error en importación SICOSS Cálculo', [
                'error' => $e->getMessage(),
                'file' => $data['file'] ?? 'No file specified'
            ]);

            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }

    private function updateImportProgress(array $progressData): void
    {
        $this->importProgress = $progressData['percentage'] ?? 0;
        $this->processedRecords = $progressData['processed'] ?? 0;
        $this->memoryUsage = $progressData['memory'] ?? 0;

        $this->dispatch('import-progress-updated', [
            'progress' => $this->importProgress,
            'processed' => $this->processedRecords,
            'memory' => $this->memoryUsage
        ]);
    }

    private function handleImportResult(array $result): void
    {
        if (empty($result['errors'])) {
            Notification::make()
                ->success()
                ->title('Importación exitosa')
                ->body("Se importaron {$result['imported']} registros")
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->warning()
                ->title('Importación con errores')
                ->body(implode("\n", $result['errors']))
                ->persistent()
                ->send();
        }
    }

    private function getYearOptions(): array
    {
        $currentYear = Carbon::now()->year;
        return array_combine(
            range($currentYear - 5, $currentYear + 1),
            range($currentYear - 5, $currentYear + 1)
        );
    }

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
            12 => 'Diciembre'
        ];
    }
}
