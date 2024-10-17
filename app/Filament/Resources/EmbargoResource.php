<?php

namespace App\Filament\Resources;

use Filament\Actions;
use Filament\Tables\Table;
use AllowDynamicProperties;
use App\Tables\EmbargoTable;
use Filament\Resources\Resource;
use App\Models\EmbargoProcesoResult;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\EmbargoResource\Pages;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;


#[AllowDynamicProperties] class EmbargoResource extends Resource
{
    protected static ?string $model = EmbargoProcesoResult::class;
    protected static ?string $modelLabel = 'Embargo';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected EmbargoTable $embargoTable;
    protected int $nroLiquiProxima;
    protected array $nroComplementarias;
    protected int $nroLiquiDefinitiva;
    protected bool $insertIntoDh25;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Nro. Liquidación'),
                TextColumn::make('tipo_embargo')->label('Tipo Embargo'),
                TextColumn::make('nro_legaj')->label('Nro. Legajo'),
                TextColumn::make('remunerativo')->money('ARS'),
                TextColumn::make('no_remunerativo')->money('ARS'),
                TextColumn::make('total')->money('ARS'),
                TextColumn::make('codn_conce')->label('Código Concepto'),
            ])
            ->defaultSort('nro_legaj')
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PeriodoFiscalSelectorWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmbargos::route('/'),
        ];
    }

    public function boot(EmbargoTable $embargoTable): void
    {
        $this->embargoTable = $embargoTable;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return EmbargoProcesoResult::getEmptyQuery();
    }

    public static function getActions(): array
    {
        return [
            Actions\Action::make('actualizar')
                ->label('Actualizar Datos')
                ->action(fn() => self::actualizarDatos())
                ->button(),
        ];
    }

    public static function actualizarDatos()
    {
        // Aquí implementaremos la lógica para obtener los parámetros
        // y actualizar los datos usando EmbargoProcesoResult::updateData()
    }
}
