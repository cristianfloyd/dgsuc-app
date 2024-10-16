<?php

namespace App\Filament\Resources;

use AllowDynamicProperties;
use App\Filament\Resources\EmbargoResource\Pages;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Models\EmbargoProcesoResult;
use App\Tables\EmbargoTable;
use Filament\Resources\Resource;
use Filament\Tables\Table;


#[AllowDynamicProperties] class EmbargoResource extends Resource
{
    protected static ?string $model = EmbargoProcesoResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected EmbargoTable $embargoTable;
    protected int $nroLiquiProxima;
    protected array $nroComplementarias;
    protected int $nroLiquiDefinitiva;
    protected bool $insertIntoDh25;

    public static function table(Table $table): Table
    {
        return (new EmbargoTable())->table($table);
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
}
