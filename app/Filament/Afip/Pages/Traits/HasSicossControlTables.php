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
            'diferencias_cuils' => $this->getCuilsColumns(),
            'diferencias_aportes' => $this->getAportesColumns(),
            'diferencias_contribuciones' => $this->getContribucionesColumns(),
            'diferencias_art' => $this->getArtColumns(),
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
                ->copyable()
                ->copyMessage('CUIL copiado')
                ->copyMessageDuration(1500),
            TextColumn::make('aportesijpdh21')
                ->label('Aportes SIJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('aporteinssjpdh21')
                ->label('Aportes INSSJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('aportesijp')
                ->label('Aportes SIJP SICOSS')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('aporteinssjp')
                ->label('Aportes INSSJP SICOSS')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('total_aportes_dh21')
                ->label('Total Aportes DH21')
                ->money('ARS')
                ->state(fn ($record) => $record->aportesijpdh21 + $record->aporteinssjpdh21)
                ->sortable(),
            TextColumn::make('total_aportes_sicoss')
                ->label('Total Aportes SICOSS')
                ->money('ARS')
                ->state(fn ($record) => $record->aportesijp + $record->aporteinssjp)
                ->sortable(),
            TextColumn::make('diferencia')
                ->money('ARS')
                ->sortable()
                ->color(fn($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn($state) => $state < 0 ? 'Falta aportar' : 'Exceso de aportes'),
        ];
    }

    protected function getContribucionesColumns(): array
    {
        return [
            TextColumn::make('cuil')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('CUIL copiado')
                ->copyMessageDuration(1500),
            TextColumn::make('contribucionsijpdh21')
                ->label('Contribución SIJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('contribucioninssjpdh21')
                ->label('Contribución INSSJP DH21')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('contribucionsijp')
                ->label('Contribución SIJP')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('contribucioninssjp')
                ->label('Contribución INSSJP')
                ->money('ARS')
                ->sortable(),
            TextColumn::make('diferencia')
                ->money('ARS')
                ->sortable()
                ->color(fn($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn($state) => $state < 0 ? 'Falta contribuir' : 'Exceso de contribuciones'),
        ];
    }

    protected function getArtColumns(): array
    {
        return [
            TextColumn::make('cuil')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('CUIL copiado')
                ->copyMessageDuration(1500),
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

    protected function getDiferenciasCuilsColumns(): array
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
                ->label('Origen')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'DH21' => 'warning',
                    'SICOSS' => 'danger',
                    default => '',
                }),
            TextColumn::make('fecha_control')
                ->label('Fecha de Control')
                ->dateTime('d/m/Y H:i:s'),
        ];
    }
    protected function getQueryForActiveTab(): Builder
    {
        return match ($this->activeTab) {
            'cuils' => DB::table('suc.control_cuils_diferencias'),
            'aportes' => DB::table('suc.control_aportes_diferencias'),
            'contribuciones' => DB::table('suc.control_contribuciones_diferencias'),
            'art' => DB::table('suc.control_art_diferencias'),
            default => DB::table('suc.control_cuils_diferencias')->whereRaw('1=0'),
        };
    }
}
