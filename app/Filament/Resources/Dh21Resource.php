<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Dh21;
use Filament\Tables;
use Livewire\Livewire;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Livewire\Reportes\OrdenPagoReporte;
use App\Filament\Resources\Dh21Resource\Pages;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21LegajoCounter;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21Concepto101Total;

class Dh21Resource extends Resource
{
    protected static ?string $model = Dh21::class;
    protected static ?string $modelLabel = 'Liquidaciones';
    protected static ?string $navigationLabel = 'Liquidaciones';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Liquidaciones';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impp_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_conce')->toggleable()->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('nov1_conce')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nov2_conce')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_orimp')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipoescalafon')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('nrogrupoesc')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('codigoescalafon')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('codc_regio')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('codc_uacad')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('codn_area')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('codn_subar')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_fuent')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_progr')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_subpr')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_proye')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_activ')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_obra')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_final')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_funci')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ano_retro')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mes_retro')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('detallenovedad')->toggleable()->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('codn_grupo_presup')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_ejercicio')->toggleable()->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('codn_subsubar')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make(name: 'gerearReporte')
                    ->label('Generar Reporte')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (Dh21 $record) {
                        static::generarReporte($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nro_legaj', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5, 10, 25, 50, 100, 250, 500, 1000])
        ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDh21s::route('/'),
            'create' => Pages\CreateDh21::route('/create'),
            'edit' => Pages\EditDh21::route('/{record}/edit'),
            'conceptos-totales' => Pages\ConceptosTotales::route('/conceptos-totales'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Dh21LegajoCounter::class,
            Dh21Concepto101Total::class,
        ];
    }

    public static function generarReporte($record)
    {

        // Verificamos que el registro tenga un ID válido
        if (!$record->nro_liqui) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo generar el reporte. Liquidación inválida.')
                ->danger()
                ->send();
            return;
        }

        // Renderizamos el componente Livewire en un modal
        return Action::make('verReporte')
            ->label('Ver Reporte')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->modalHeading('Reporte de Orden de Pago')
            ->modalContent(
                fn() => Livewire::mount(
                    name: OrdenPagoReporte::class, params: ['liquidacionId' => $record->nro_liqui]
                    ))
            ->modalWidth('7xl');
    }

    protected static function descargarReportePDF($liquidacionId)
    {
        $reporteHtml = Livewire::mount(OrdenPagoReporte::class, ['liquidacionId' => $liquidacionId]);
        $nombreArchivo = 'orden_pago_' . $liquidacionId . '_' . now()->format('YmdHis') . '.pdf';
        $pdf = Pdf::loadHTML($reporteHtml);
        return response()->streamDownload(
            fn() => print($pdf->output()),
            $nombreArchivo,
            ['Content-Type' => 'application/pdf']
        );
    }



    // Añadir la acción en el header
    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrirModal')
                ->label('Abrir Modal')
                ->icon('heroicon-o-plus')
                ->modalContent() // Contenido vacío
                ->modalDescription('modal de preuba') // Contenido vacío
                ->modalWidth('7xl'), // Ancho del modal
        ];
    }
}
