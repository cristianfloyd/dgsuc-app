<?php

namespace App\Filament\Afip\Pages\Traits;

use Carbon\Carbon;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait HasSicossControlTables
{
    protected function getColumnsForActiveTab(): array
    {
        return match ($this->activeTab) {
            'diferencias_cuils' => $this->getCuilsColumns(),
            'diferencias_aportes' => $this->getAportesColumns(),
            'diferencias_contribuciones' => $this->getContribucionesColumns(),
            'diferencias_art' => $this->getArtColumns(),
            'conceptos' => $this->getConceptosColumns(),
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
                ->color(fn (string $state): string => match ($state) {
                    'DH21' => 'warning',
                    'SICOSS' => 'danger',
                })
                ->description(fn (string $state): string => match ($state) {
                    'DH21' => 'Existe en DH21 pero no en SICOSS',
                    'SICOSS' => 'Existe en SICOSS pero no en DH21',
                    default => ''
                }),
        ];
    }

    protected function getAportesColumns(): array
    {
        return [
            TextColumn::make('dh01.nro_legaj')
                ->label('Legajo')
                ->alignment(Alignment::Right)->size(TextColumn\TextColumnSize::Small)
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Legajo copiado')
                ->copyMessageDuration(1500),
            TextColumn::make('cuil')
                ->searchable()
                ->alignment(Alignment::Right)->size(TextColumn\TextColumnSize::Small)
                ->sortable()
                ->copyable()
                ->copyMessage('CUIL copiado')
                ->copyMessageDuration(1500),
            TextColumn::make('mapucheSicoss.cod_act')
                ->label('Cod. Act.')
                ->alignment(Alignment::Center)->size(TextColumn\TextColumnSize::Small),
            TextColumn::make('aportesijpdh21')
                ->label('SIJP DH21')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->sortable(),
            TextColumn::make('aporteinssjpdh21')
                ->label('INSSJP DH21')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->sortable(),
            TextColumn::make('aportesijp')
                ->label('SIJP SICOSS')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->sortable(),
            TextColumn::make('aporteinssjp')
                ->label('INSSJP SICOSS')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->sortable(),
            TextColumn::make('total_aportes_dh21')
                ->label('Total Aportes DH21')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->state(fn ($record) => $record->aportesijpdh21 + $record->aporteinssjpdh21)
                ->sortable(),
            TextColumn::make('total_aportes_sicoss')
                ->label('Total Aportes SICOSS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->money('ARS')
                ->state(fn ($record) => $record->aportesijp + $record->aporteinssjp + $record->sicossCalculo->aportediferencialsijp + $record->sicossCalculo->aportesres33_41re)
                ->sortable(),
            TextColumn::make('diferencia')
                ->money('ARS')
                ->alignment(Alignment::End)->size(TextColumn\TextColumnSize::ExtraSmall)
                ->sortable()
                ->color(fn ($state) => $state < 0 ? 'danger' : 'warning')
                ->tooltip(fn ($state) => $state < 0 ? 'Falta aportar' : 'Exceso de aportes'),
        ];
    }

    protected function getContribucionesColumns(): array
    {
        return [
            TextColumn::make('dh01.nro_legaj')
                ->label('Legajo')
                ->sortable()
                ->copyable()
                ->copyMessage('Legajo copiado')
                ->copyMessageDuration(1500),
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
                ->color(fn ($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn ($state) => $state < 0 ? 'Falta contribuir' : 'Exceso de contribuciones'),
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
                ->color(fn ($state) => $state < 0 ? 'danger' : 'warning')
                ->description(fn ($state) => abs($state)),
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
                ->color(fn (string $state): string => match ($state) {
                    'DH21' => 'warning',
                    'SICOSS' => 'danger',
                    default => '',
                }),
            TextColumn::make('fecha_control')
                ->label('Fecha de Control')
                ->dateTime('d/m/Y H:i:s'),
        ];
    }

    protected function getConceptosColumns(): array
    {
        return [
            TextColumn::make('codn_conce')
                ->label('Código')
                ->searchable()
                ->sortable(),

            TextColumn::make('desc_conce')
                ->label('Descripción')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('importe')
                ->label('Importe')
                ->money('ARS')
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Fecha de Control')
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getQueryForActiveTab(): Builder
    {
        return match ($this->activeTab) {
            'cuils' => DB::table('suc.control_cuils_diferencias'),
            'aportes' => DB::table('suc.control_aportes_diferencias'),
            'contribuciones' => DB::table('suc.control_contribuciones_diferencias'),
            'art' => DB::table('suc.control_art_diferencias'),
            'conceptos' => DB::table('suc.control_conceptos_periodos')
                ->where('year', $this->year ?? Carbon::now()->year)
                ->where('month', $this->month ?? Carbon::now()->month),
            default => DB::table('suc.control_cuils_diferencias')->whereRaw('1=0'),
        };
    }
}
