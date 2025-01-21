<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Models\ComprobanteNominaModel;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ComprobanteNominaService;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\RelationManagers;
use App\Filament\Resources\ComprobanteNominaModelResource\Pages\ImportComprobanteNomina;

class ComprobanteNominaModelResource extends Resource
{
    protected static ?string $model = ComprobanteNominaModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';
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
                            // ->rules([
                            //     fn() => function($attribute, $value, $fail) {
                            //         if (!preg_match('/^che\d{4}/', basename($value))) {
                            //             $fail("El archivo debe comenzar con 'che' seguido del año y mes (ejemplo: che2412)");
                            //         }
                            //     }
                            // ])
                            ->required()
                            ->maxSize(5120)
                            ->directory('comprobantes-temp')
                            ->preserveFilenames()
                    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->headerActions([
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
                            ->helperText('Seleccione el archivo CHE para procesar')
                    ])
                    ->action(function (array $data, ComprobanteNominaService $service): void {
                        try {
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
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error en la importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->columns([
                Tables\Columns\TextColumn::make('anio_periodo')
                    ->label('Año')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('mes_periodo')
                    ->label('Mes')
                    ->formatStateUsing(fn($state) => nombreMes($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero_liquidacion')
                    ->label('Liquidación')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion_liquidacion')
                    ->label('Descripción')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('importe_neto')
                    ->label('Importe Neto')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_administrativa')
                    ->label('Área')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion_retencion')
                    ->label('Retención')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('importe_retencion')
                    ->label('Importe Retención')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('anio_periodo')
                    ->label('Año')
                    ->options(fn() => ComprobanteNominaModel::distinct()
                        ->pluck('anio_periodo', 'anio_periodo')
                        ->toArray()),

                SelectFilter::make('mes_periodo')
                    ->label('Mes')
                    ->options(fn() => collect(range(1, 12))->mapWithKeys(
                        fn($mes) =>
                        [$mes => nombreMes($mes)]
                    )->toArray()),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComprobanteNominaModels::route('/'),
            'import' => Pages\ImportComprobanteNomina::route('/import'),

        ];
    }
}
