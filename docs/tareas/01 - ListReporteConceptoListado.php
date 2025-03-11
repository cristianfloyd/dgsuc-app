// ... existing code ...

use Illuminate\Support\Str;
use App\Jobs\GenerateExcelExportJob;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ListReporteConceptoListados extends ListRecords
{
    use ConceptoListadoTabs;
    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($livewire) {
                    $query = $this->getFilteredSortedTableQuery();

                    // Comprobar que el query no esté vacío
                    if ($query->count() == 0) {
                        Notification::make()
                            ->danger()
                            ->title('Sin resultados')
                            ->body('No se encontraron registros con los filtros seleccionados.')
                            ->send();
                        return;
                    }

                    // Generar un nombre de archivo único
                    $filename = 'reporte_concepto_listado_' . Str::random(10) . '.xlsx';
                    $filepath = 'exports/' . $filename;

                    // Obtener los filtros actuales para pasarlos al job
                    $filters = $livewire->tableFilters;

                    // Despachar el job para generar el Excel en segundo plano
                    GenerateExcelExportJob::dispatch(
                        get_class($query->getModel()),
                        $filters,
                        $filepath,
                        auth()->id()
                    );

                    Notification::make()
                        ->success()
                        ->title('Exportación iniciada')
                        ->body('El archivo Excel se está generando. Recibirás una notificación cuando esté listo para descargar.')
                        ->persistent()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('¿Desea iniciar la exportación?')
                ->modalDescription('Se generará un archivo Excel con los datos filtrados. Este proceso puede tomar varios minutos dependiendo de la cantidad de registros.')
                ->modalSubmitActionLabel('Iniciar exportación'),
        ];
    }
}