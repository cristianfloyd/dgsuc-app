<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use Filament\Forms\Form;
use App\Imports\DataImport;
use Filament\Actions\Action;
use Livewire\WithFileUploads;
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
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Reportes\Resources\ImportDataResource;

class ImportData extends Page
{
    use MapucheConnectionTrait;
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = ImportDataResource::class;
    protected static string $view = 'filament.reportes.resources.import-data-resource.pages.import-data';

    public array $data = [];

    public function mount(): void
    {
        $tableService = new ImportDataTableService();
        $tableService->ensureTableExists();
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
        $connection = $this->getConnectionFromTrait();
        $filePath = collect($this->data['excel_file'])->first()->getRealPath();

        try {
            $connection->beginTransaction();
            Log::info('Importando archivo: ' . $filePath);

            $importer = new DataImport($this->data['nro_liqui']);
            Excel::import($importer, $filePath);

            $connection->commit();

            Notification::make()
                ->title('Importación exitosa')
                ->success()
                ->send();

        } catch (\Exception $e) {
            $connection->rollBack();
            Log::error('Error en la importación: ' . $e->getMessage());
            Notification::make()
                ->title('Error en la importación')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Excel')
                ->action('import')
                ->requiresConfirmation()
                ->modalHeading('¿Confirmar importación?')
                ->modalDescription('¿Estás seguro de que deseas importar este archivo?')
                ->modalSubmitActionLabel('Sí, importar')
        ];
    }
}
