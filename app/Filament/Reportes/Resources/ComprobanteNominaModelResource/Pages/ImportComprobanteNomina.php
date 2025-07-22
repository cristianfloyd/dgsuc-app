<?php

namespace app\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use App\Filament\Reportes\Resources\ComprobanteNominaModelResource;
use App\Services\ComprobanteNominaService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;

class ImportComprobanteNomina extends Page
{
    public ?array $data = [];

    protected static string $resource = ComprobanteNominaModelResource::class;

    protected static string $view = 'filament.resources.comprobante-nomina.pages.import';

    protected static ?string $title = 'Importación Avanzada';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración de Importación')
                    ->description('Configure las opciones de importación del archivo')
                    ->schema([
                        FileUpload::make('archivo')
                            ->label('Archivo CHE')
                            ->helperText('Formato esperado: cheAAMM.NNNN')
                            ->required()
                            ->maxSize(5120)
                            ->directory('comprobantes-temp'),

                        Toggle::make('validar_estructura')
                            ->label('Validar estructura del archivo')
                            ->default(true)
                            ->helperText('Verifica el formato antes de importar'),

                        Toggle::make('corregir_negativos')
                            ->label('Corregir importes negativos')
                            ->default(true)
                            ->helperText('Ajusta automáticamente los valores negativos'),

                        Select::make('modo_importacion')
                            ->label('Modo de importación')
                            ->options([
                                'append' => 'Agregar al existente',
                                'replace' => 'Reemplazar todo',
                            ])
                            ->default('append')
                            ->required(),
                    ]),
            ]);
    }

    public function import(): void
    {
        $data = $this->form->getState();

        try {
            $service = new ComprobanteNominaService();

            if (!$service->checkTableExists()) {
                $service->createTable();
            }

            if ($data['modo_importacion'] === 'replace') {
                $service->truncateTable();
            }

            $stats = $service->processFile(
                storage_path('app/public/' . $data['archivo']),
            );

            Notification::make()
                ->title('Importación completada')
                ->body("Procesados: {$stats['procesados']}, Errores: {$stats['errores']}")
                ->success()
                ->send();

            $this->redirect(ComprobanteNominaModelResource::getUrl('index'));
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            Notification::make()
                ->title('Error en la importación')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
