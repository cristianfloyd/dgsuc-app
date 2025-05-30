<?php

namespace App\Filament\Bloqueos\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use App\Models\Mapuche\Bloqueos\RepBloqueo;

class BloqueosHistorialResource extends Resource
{
    protected static ?string $model = RepBloqueo::class;
    protected static ?string $label = 'Historial de Bloqueos';
    protected static ?string $pluralLabel = 'Historial de Bloqueos';
    protected static ?string $navigationGroup = 'Consultas';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?int $navigationSort = 90;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Período Fiscal')->sortable()->searchable(),
                TextColumn::make('nro_legaj')->label('Legajo')->sortable()->searchable(),
                TextColumn::make('nro_cargo')->label('Cargo')->sortable()->searchable(),
                TextColumn::make('nombre')->label('Nombre')->searchable(),
                TextColumn::make('dependencia')->label('Dependencia')->searchable(),
                TextColumn::make('tipo')->label('Tipo')->badge()->sortable(),
                TextColumn::make('fecha_baja')->label('Fecha Baja')->date('Y-m-d')->sortable(),
                TextColumn::make('estado')->label('Estado')->badge()->sortable(),
                TextColumn::make('fecha_procesamiento')->label('Fecha Archivado')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('procesado_por')->label('Archivado por')->sortable(),
                TextColumn::make('observaciones')->label('Obs.')->limit(20)->tooltip(fn($record) => $record->observaciones),
                IconColumn::make('chkstopliq')->label('Stop')->boolean(),
            ])
            ->filters([
                Filter::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->query(fn($query, $value) => $query->where('nro_liqui', $value)),
                Filter::make('tipo')
                    ->label('Tipo')
                    ->query(fn($query, $value) => $query->where('tipo', $value)),
                Filter::make('estado')
                    ->label('Estado')
                    ->query(fn($query, $value) => $query->where('estado', $value)),
                Filter::make('fecha_procesamiento')
                    ->label('Fecha Archivado')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('to')->label('Hasta'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from']) {
                            $query->whereDate('fecha_procesamiento', '>=', $data['from']);
                        }
                        if ($data['to']) {
                            $query->whereDate('fecha_procesamiento', '<=', $data['to']);
                        }
                    }),
            ])
            ->defaultSort('fecha_procesamiento', 'desc')
            ->paginated();
    }

    public static function getPages(): array
    {
        return [
            'index' => BloqueosHistorialResource\Pages\ListBloqueosHistorial::route('/'),
            'view' => BloqueosHistorialResource\Pages\ViewBloqueoHistorial::route('/{record}'),
        ];
    }
}
