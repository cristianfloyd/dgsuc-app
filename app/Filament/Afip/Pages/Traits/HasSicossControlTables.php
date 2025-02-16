<?php

namespace App\Filament\Afip\Pages\Traits;

use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Columns\TextColumn;

trait HasSicossControlTables
{
    protected function getColumnsForActiveTab(): array
    {
        return match ($this->activeTab) {
            'cuils' => $this->getCuilsColumns(),
            'aportes' => $this->getAportesColumns(),
            'art' => $this->getArtColumns(),
            default => [],
        };
    }

    protected function getCuilsColumns(): array
    {
        return [
            TextColumn::make('cuil')
                ->label('CUIL')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('CUIL copiado')
                ->copyMessageDuration(1500),
            TextColumn::make('origen')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'DH21' => 'warning',
                    'SICOSS' => 'danger',
                })
                ->description(fn(string $state): string => match ($state) {
                    'DH21' => 'Existe en DH21 pero no en SICOSS',
                    'SICOSS' => 'Existe en SICOSS pero no en DH21',
                    default => ''
                }),
        ];
    }

    protected function getAportesColumns(): array
    {
        return [
            TextColumn::make('cuil')
                ->searchable()
                ->sortable()
                ->copyable(),
            TextColumn::make('aportesijpdh21')
                ->label('Aportes SIJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('aporteinssjpdh21')
                ->label('Aportes INSSJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('diferencia')
                ->money('ARS')
                ->sortable()
                ->color(fn($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn($state) => $state < 0 ? 'Falta aportar' : 'Exceso de aportes'),
        ];
    }

    protected function getArtColumns(): array
    {
        return [
            TextColumn::make('cuil')
                ->searchable()
                ->sortable()
                ->copyable(),
            TextColumn::make('art_contrib')
                ->label('Contribución ART')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('calculo_teorico')
                ->label('Cálculo Teórico')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('diferencia')
                ->money('ARS')
                ->sortable()
                ->color(fn($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn($state) => abs($state)),
        ];
    }

    protected function getQueryForActiveTab(): Builder
    {
        return match ($this->activeTab) {
            'cuils' => DB::table('suc.control_cuils_diferencias'),
            'aportes' => DB::table('suc.control_aportes_diferencias'),
            'art' => DB::table('suc.control_art_diferencias'),
            default => DB::table('suc.control_cuils_diferencias')->whereRaw('1=0'),
        };
    }
}
