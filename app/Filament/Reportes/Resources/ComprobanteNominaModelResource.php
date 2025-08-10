<?php

namespace App\Filament\Reportes\Resources;

use App\Exports\ComprobantesNominaExport;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;
use App\Models\ComprobanteNominaModel;
use App\Services\ComprobanteNominaService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComprobanteNominaModelResource extends Resource
{
    protected static ?string $model = ComprobanteNominaModel::class;

    protected static ?string $navigationGroup = 'Informes';

    protected static ?string $modelLabel = 'CHE';

    protected static ?string $pluralModelLabel = 'CHE';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('archivo')
                            ->label('Archivo CHE')
                            ->helperText('Formato esperado: cheAAMM.NNNN')
                            ->required()
                            ->maxSize(5120)
                            ->directory('comprobantes-temp')
                            ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->headerActions([
                Action::make('exportar_pdf_barry')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (array $data): StreamedResponse {
                        $liquidacion = ComprobanteNominaModel::first();

                        return response()->streamDownload(
                            function () use ($liquidacion): void {
                                echo ComprobanteNominaService::exportarPdf($liquidacion);
                            },
                            "comprobantes_{$liquidacion->nro_liqui}.pdf",
                        );
                    }),
                Action::make('exportar')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (array $data): BinaryFileResponse {
                        $liquidacion = ComprobanteNominaModel::first();

                        return Excel::download(
                            new ComprobantesNominaExport(
                                $liquidacion->nro_liqui,
                                $liquidacion->desc_liqui,
                            ),
                            "comprobantes_{$liquidacion->nro_liqui}.xlsx",
                        );
                    }),
                Action::make('importar')
                    ->label('Importar Rápido')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('archivo')
                            ->label('Archivo CHE')
                            ->helperText('Formato esperado: cheAAMM.NNNN')
                            ->required()
                            ->maxSize(5120)
                            ->directory('comprobantes-temp')
                            ->preserveFilenames()
                            ->helperText('Seleccione el archivo CHE para procesar'),
                    ])
                    ->action(function (array $data, ComprobanteNominaService $service): void {
                        try {
                            if (!$service->checkTableExists()) {
                                $service->createTable();
                            }

                            $stats = $service->processFile(
                                storage_path('app/public/' . $data['archivo']),
                            );

                            Notification::make()
                                ->title('Importación completada')
                                ->body("Procesados: {$stats['procesados']}, Errores: {$stats['errores']}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error en la importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('importar_avanzado')
                    ->label('Importacion Avanzada')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->url(fn () => static::getUrl('import')),
                Action::make('generate')
                    ->label('Generar Comprobantes')
                    ->icon('heroicon-o-document-check')
                    ->url(fn (): string => static::getUrl('generate')),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('anio_periodo')
                    ->label('Año')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mes_periodo')
                    ->label('Mes')
                    ->formatStateUsing(fn ($state) => nombreMes($state)),

                Tables\Columns\TextColumn::make('nro_liqui')
                    ->label('Nro Liqui')
                    ->searchable(),

                Tables\Columns\TextColumn::make('desc_liqui')
                    ->label('Desc. Liquidación')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->desc_liqui)
                    ->searchable(),

                Tables\Columns\TextColumn::make('importe')
                    ->label('Importe')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_administrativa')
                    ->label('Área')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('descripcion_retencion')
                    ->label('Descripción')
                    ->searchable()
                    ->toggleable(),


            ])
            ->filters([
                SelectFilter::make('anio_periodo')
                    ->label('Año')
                    ->options(fn () => ComprobanteNominaModel::distinct()
                        ->pluck('anio_periodo', 'anio_periodo')
                        ->toArray()),

                SelectFilter::make('mes_periodo')
                    ->label('Mes')
                    ->options(fn () => collect(range(1, 12))->mapWithKeys(
                        fn ($mes) =>
                        [$mes => nombreMes($mes)],
                    )->toArray()),

            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComprobanteNominaModels::route('/'),
            'import' => Pages\ImportComprobanteNomina::route('/import'),
            'generate' => Pages\GenerateComprobanteNomina::route('/generate'),
        ];
    }
}
