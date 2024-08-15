<?php

namespace App\Filament\Resources\Dh21Resource\Pages;

use App\Models\Dh21;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Resources\Dh21Resource;
use Filament\Tables\Concerns\InteractsWithTable;

class ConceptosTotales extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = Dh21Resource::class;

    protected static string $view = 'filament.resources.dh21-resource.pages.conceptos-totales';

    public function table(Table $table): Table
    {
        return $table
            ->query(Dh21::conceptosTotales())
            ->columns([
                TextColumn::make('id_liquidacion')->hidden(),
                TextColumn::make('codn_conce')
                    ->label('Código de Concepto')
                    ->sortable(),
                TextColumn::make('total_impp')
                    ->label('Total Importe')
                    ->money('ARS')
                    ->sortable(),
            ]);
    }


}
