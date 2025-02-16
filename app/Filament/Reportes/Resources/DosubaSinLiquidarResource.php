<?php

namespace App\Filament\Reportes\Resources;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Exports\DosubaSinLiquidarExport;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Infolists\Components\IconEntry;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

class DosubaSinLiquidarResource extends Resource
{
    protected static ?string $model = DosubaSinLiquidarModel::class;
    protected static ?string $label = 'Dosuba Sin Liquidar - Reporte';
    protected static ?string $navigationLabel = 'Dosuba Sin Liquidar';
    protected static ?string $slug = 'reportes/dosuba-sin-liquidar';
    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parámetros del Reporte')
                    ->schema([
                        Select::make('liquidacion_base')
                            ->label('Liquidación Base')
                            ->options(
                                Dh22::query()
                                    ->definitiva()
                                    ->orderByDesc('nro_liqui')
                                    ->limit(5)
                                    ->pluck('desc_liqui', 'nro_liqui')
                            )
                            ->searchable(),
                    ])
            ]);
    }


    public static function table(Table $table): Table
    {
        // Verificamos si hay datos para la sesión actual
        $hasData = DosubaSinLiquidarModel::where('session_id', session()->getId())->exists();

        return $table
            ->query(fn () => $hasData ? static::getModel()::query() : static::getModel()::query()->whereRaw('1 = 0'))
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->sortable(),
                TextColumn::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->sortable(),
                TextColumn::make('ultima_liquidacion')
                    ->label('Última Liquidación')
                    ->sortable(),
                IconColumn::make('embarazada')
                    ->label('Embarazada')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('fallecido')
                    ->label('Fallecido')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Período')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($records) {
                        try {
                            return Excel::download(
                                new DosubaSinLiquidarExport($records, $records->first()->periodo_fiscal),
                                'dosuba-sin-liquidar-' . now()->format('Y-m-d') . '.xlsx'
                            );
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al exportar')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->headerActions([
                ActionGroup::make([
                    // ... other actions ...
                    Action::make('export')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->form([
                            Select::make('periodo')
                                ->label('Período')
                                ->options(function() {
                                    $options = [];
                                    $date = Carbon::now();

                                    for ($i = 0; $i < 12; $i++) {
                                        $periodo = $date->format('Ym');
                                        $options[$periodo] = $date->format('Y/m');
                                        $date->subMonth();
                                    }

                                    return $options;
                                })
                                ->default(fn() => Carbon::now()->subMonth()->format('Ym'))
                                ->required()
                        ])
                        ->action(function (array $data) {
                            return Excel::download(
                                new DosubaSinLiquidarExport(
                                    records: DosubaSinLiquidarModel::all(),
                                    periodo: $data['periodo']
                                ),
                                'dosuba-sin-liquidar-' . $data['periodo'] . '.xlsx'
                            );
                        }),
                ])->label('Acciones de Tabla')
                  ->icon('heroicon-o-cog'),
            ])
            ->emptyStateHeading('No hay datos disponibles')
            ->emptyStateDescription('Genera un nuevo reporte para ver los resultados.')
            ->emptyStateIcon('heroicon-o-document-chart-bar');
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
            'index' => Pages\ListDosubaSinLiquidars::route('/'),
            'create' => Pages\CreateDosubaSinLiquidar::route('/create'),
        ];
    }
}
