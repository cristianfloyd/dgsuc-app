<?php

namespace App\Filament\Bloqueos\Resources\BloqueosHistorials\BloqueosHistorials;

use App\Filament\Bloqueos\Resources\BloqueosHistorials\Pages\ListBloqueosHistorial;
use App\Filament\Bloqueos\Resources\BloqueosHistorials\Pages\ViewBloqueoHistorial;
use App\Models\Mapuche\Bloqueos\RepBloqueo;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use UnitEnum;

class BloqueosHistorialResource extends Resource
{
    protected static ?string $model = RepBloqueo::class;

    protected static ?string $label = 'Historial de Bloqueos';

    protected static ?string $pluralLabel = 'Historial de Bloqueos';

    protected static string|UnitEnum|null $navigationGroup = 'Consultas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 90;

    #[\Override]
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
                    ->schema([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('to')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data): void {
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

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBloqueosHistorial::route('/'),
            'view' => ViewBloqueoHistorial::route('/{record}'),
        ];
    }
}
