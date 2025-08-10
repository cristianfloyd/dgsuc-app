<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

use App\Filament\Reportes\Resources\DosubaSinLiquidarResource;
use App\Models\Reportes\DosubaSinLiquidarModel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class ListDosubaSinLiquidars extends ListRecords
{
    protected static string $resource = DosubaSinLiquidarResource::class;

    public function mount(): void
    {
        parent::mount();

        // Aseguramos que la tabla exista antes de cualquier operación
        DosubaSinLiquidarModel::createTableIfNotExists();
        // Limpiamos registros antiguos
        DosubaSinLiquidarModel::cleanOldRecords();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Generar Reporte'),
            Action::make('documentation')
                ->label('Documentación')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->modalHeading('Documentación del Reporte Dosuba Sin Liquidar')
                ->modalContent(function () {
                    $markdownPath = base_path('resources/docs/documentacion-dosuba-sin-liquidar.md');

                    if (!File::exists($markdownPath)) {
                        return 'La documentación no está disponible en este momento.';
                    }

                    $markdown = File::get($markdownPath);

                    $converter = new GithubFlavoredMarkdownConverter([
                        'html_input' => 'strip',
                        'allow_unsafe_links' => false,
                    ]);

                    $html = $converter->convert($markdown);

                    // Agregar estilos para mejorar la presentación
                    $styledHtml = '
                        <div class="prose prose-sm md:prose-base lg:prose-lg max-w-none dark:prose-invert prose-headings:font-bold prose-headings:text-primary-600 dark:prose-headings:text-primary-400 prose-img:rounded-xl prose-img:shadow-md">
                            ' . $html . '
                        </div>
                    ';

                    return new HtmlString($styledHtml);
                })
                ->modalWidth('5xl')
                ->modalIcon('heroicon-o-information-circle'),
            Action::make('vaciarTabla')
                ->label('Vaciar Tabla')
                ->action(function (): void {
                    DosubaSinLiquidarModel::clearSessionData();
                    Notification::make()->success()->title('La tabla ha sido vaciada exitosamente.')->send();
                })
                ->color('danger') // Puedes cambiar el color según tu preferencia
                ->requiresConfirmation() // Solicita confirmación antes de ejecutar la acción
                ->modalHeading('Confirmar Vaciar Tabla')
                ->modalDescription('¿Estás seguro de que deseas vaciar la tabla? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Vaciar'),
        ];
    }
}
