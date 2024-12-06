<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

class DosubaSinLiquidarResource extends Resource
{
    protected static ?string $model = DosubaSinLiquidarModel::class;

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
                            ->required()
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
                TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->sortable(),
                TextColumn::make('ultima_liquidacion')
                    ->label('Última Liquidación')
                    ->sortable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Período')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Exportar a Excel')
                    ->action(fn ($records) => static::exportToExcel($records))
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
