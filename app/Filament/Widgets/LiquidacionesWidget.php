<?php

namespace App\Filament\Widgets;

use App\Models\Dh22;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class LiquidacionesWidget extends BaseWidget
{
    protected function getTablwQuery()
    {
        return Dh22::getLiquidacionesForWidget();
    }
    public function table(Table $table): Table
    {
        return $table
        ->query($this->getTablwQuery())
        ->columns( [
            TextColumn::make('nro_liqui')
                ->label('Número de Liquidación')
                ->sortable(),
            TextColumn::make('desc_liqui')
                ->label('Descripción')
                ->searchable(),
            TextColumn::make('tipoLiquidacion.desc_corta')
                ->label('Tipo de Liquidación'),
            TextColumn::make('fec_emisi')
                ->label('Fecha de Emisión')
                ->date(),
        ]);
    }
}
