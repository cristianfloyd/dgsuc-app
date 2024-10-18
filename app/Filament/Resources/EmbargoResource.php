<?php

namespace App\Filament\Resources;

use AllowDynamicProperties;
use App\Filament\Resources\EmbargoResource\Pages;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Models\EmbargoProcesoResult;
use App\Tables\EmbargoTable;
use App\Traits\DisplayResourceProperties;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


#[AllowDynamicProperties] class EmbargoResource extends Resource
{
    use DisplayResourceProperties;

    protected static ?string $model = EmbargoProcesoResult::class;
    protected static ?string $modelLabel = 'Embargo';
    protected static ?string $slug = 'embargos';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected EmbargoTable $embargoTable;
    protected int $nroLiquiProxima;
    protected array $nroComplementarias;
    protected int $nroLiquiDefinitiva;
    protected bool $insertIntoDh25;

    public static function boot(EmbargoTable $embargoTable): void
    {
        parent::boot();

        static::$embargoTable = $embargoTable;

        static::$nroLiquiProxima = 4; // Lógica para determinar este valor
        static::$nroComplementarias = []; // Lógica para determinar este array
        static::$nroLiquiDefinitiva = null; // Lógica para determinar este valor
        static::$insertIntoDh25 = false; // Lógica para determinar este booleano

        // Usar estas variables para actualizar los datos
        EmbargoProcesoResult::updateData(
            static::$nroComplementarias,
            static::$nroLiquiDefinitiva,
            static::$nroLiquiProxima,
            static::$insertIntoDh25
        );
    }


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
                Action::make('Procesar')
                    ->label('Actualizar Datos')
                    ->action(fn() => self::actualizarDatos())
                    ->button(),
                Action::make('configure')
                    ->label('Configure Parameters')
                    ->url(fn(): string => static::getUrl('configure'))
                    ->icon('heroicon-o-cog')
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function actualizarDatos()
    {
        // Aquí implementaremos la lógica para obtener los parámetros
        // y actualizar los datos usando EmbargoProcesoResult::updateData()
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
            'dashboard' => Pages\DashboardEmbargo::route('/admin'),
            'configure' => Pages\ConfigureEmbargoParameters::route('/configure'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return EmbargoProcesoResult::getEmptyQuery();
    }

    public static function getActions(): array
    {
        return [
            Action::make('actualizar')
                ->label('Actualizar Datos')
                ->action(fn() => self::actualizarDatos())
                ->button(),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Configure')
                ->icon('heroicon-o-cog')
                ->url(static::getUrl('configure')),
        ];
    }

    public static function getPropertiesToDisplay(): array
    {
        return [
            'nroLiquiProxima',
            'nroComplementarias',
            'nroLiquiDefinitiva',
            'insertIntoDh25',
        ];
    }
}
