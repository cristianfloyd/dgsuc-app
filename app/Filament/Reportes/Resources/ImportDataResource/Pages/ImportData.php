<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use App\Imports\DataImport;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use App\Filament\Reportes\Resources\ImportDataResource;

class ImportData extends Page
{
    protected static string $resource = ImportDataResource::class;

    protected static string $view = 'filament.reportes.resources.import-data-resource.pages.import-data';


    public function import(): void
    {
        $file = $this->form->getState()['excel_file'];
        Excel::import(new DataImport, $file);

        Notification::make()
            ->title('Importado exitosamente')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Excel')
                ->action('import')
                ->requiresConfirmation()
        ];
    }
}
