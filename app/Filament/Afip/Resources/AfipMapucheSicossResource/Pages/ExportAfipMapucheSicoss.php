<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;

use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use App\Services\Sicoss\SicossExportService;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Afip\Resources\AfipMapucheSicossResource;

class ExportAfipMapucheSicoss extends Page
{
    use InteractsWithForms;

    protected static string $resource = AfipMapucheSicossResource::class;
    protected static string $view = 'filament.resources.afip-mapuche-sicoss.pages.export';
    protected PeriodoFiscalService $periodoFiscalService;

    // Propiedades para tracking del progreso
    public $exportProgress = 0;
    public $totalRecords = 0;
    public ?array $data = [];
    public $year = null;
    public $month = null;
    public $format = 'txt';
    public $includeInactive = false;
    public $processedRecords = 0;

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
            'format' => 'txt',
            'includeInactive' => false
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Exportación de archivo SICOSS')
                ->description('Configure las opciones para exportar los datos en formato SICOSS')
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
                        Section::make('Opciones de Exportación')
                            ->schema([
                                Select::make('format')
                                    ->label('Formato')
                                    ->options([
                                        'txt' => 'Archivo TXT (SICOSS)',
                                        'excel' => 'Archivo Excel'
                                    ])
                                    ->default('txt')
                                    ->required()
                                    ->helperText('El formato TXT es compatible con el sistema SICOSS'),
                                Toggle::make('includeInactive')
                                    ->label('Incluir Inactivos')
                                    ->helperText('Incluir empleados inactivos en la exportación')
                                    ->default(false),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columns(3)
                ])
                ->collapsible()
        ];
    }

    public function export()
    {
        try {
            $data = $this->form->getState();
            $periodoFiscal = $data['year'] . sprintf('%02d', $data['month']);

            // Obtener registros según el período fiscal
            $query = static::getResource()::getModel()::query()
                ->where('periodo_fiscal', $periodoFiscal);

            // Aplicar filtro de activos/inactivos si es necesario
            if (!$data['includeInactive']) {
                $query->where('cod_situacion', '!=', '2'); // Asumiendo que '2' es el código para inactivos
            }

            $registros = $query->get();

            if ($registros->isEmpty()) {
                $this->showErrorNotification('No se encontraron registros para el período fiscal seleccionado');
                return;
            }

            $this->totalRecords = $registros->count();
            $this->processedRecords = $this->totalRecords;

            // Exportar según el formato seleccionado
            $exportService = Container::getInstance()->make(SicossExportService::class);
            $path = $exportService->generarArchivo($registros, $data['format']);

            // Notificar éxito y ofrecer descarga
            Notification::make()
                ->success()
                ->title('Exportación completada')
                ->body("Se exportaron {$this->totalRecords} registros correctamente")
                ->actions([
                    \Filament\Notifications\Actions\Action::make('download')
                        ->label('Descargar archivo')
                        ->url(route('afip.sicoss.download', ['path' => base64_encode($path)]))
                        ->openUrlInNewTab()
                ])
                ->persistent()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error en exportación SICOSS', [
                'error' => $e->getMessage(),
                'periodo' => $periodoFiscal ?? 'No especificado'
            ]);

            $this->showErrorNotification('Error durante la exportación: ' . $e->getMessage());
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

    /**
     * Genera las opciones de años para el selector
     * Muestra 5 años anteriores y 1 año posterior al actual
     */
    private function getYearOptions(): array
    {
        $currentYear = Carbon::now()->year;
        return array_combine(
            range($currentYear - 5, $currentYear + 1),
            range($currentYear - 5, $currentYear + 1)
        );
    }

    /**
     * Genera las opciones de meses para el selector
     * Retorna array asociativo con número de mes => nombre del mes
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
            12 => 'Diciembre'
        ];
    }
}
