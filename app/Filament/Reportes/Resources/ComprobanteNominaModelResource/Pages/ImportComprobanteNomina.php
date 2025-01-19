<?php

namespace app\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Services\ComprobanteNominaService;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource;

class ImportComprobanteNomina extends Page
{
    protected static string $resource = ComprobanteNominaModelResource::class;
    protected static string $view = 'filament.resources.comprobante-nomina.pages.import';
    protected static ?string $title = 'Importar Comprobantes';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('archivo')
                            ->label('Archivo CHE')
                            ->acceptedFileTypes(['.che', '.txt'])
                            ->required()
                            ->maxSize(5120)
                            ->directory('comprobantes-temp')
                            ->preserveFilenames()
                    ])
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

            $stats = $service->processFile(
                storage_path('app/public/' . $data['archivo'])
            );

            Notification::make()
                ->title('Importación completada')
                ->body("Procesados: {$stats['procesados']}, Errores: {$stats['errores']}")
                ->success()
                ->send();

            $this->redirect(ComprobanteNominaModelResource::getUrl('index'));

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error en la importación')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
