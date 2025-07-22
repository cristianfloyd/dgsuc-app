<?php

namespace App\Filament\Afip\Resources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RelacionActivaRelationManager extends RelationManager
{
    protected static string $relationship = 'relacionActiva';

    protected static ?string $recordTitleAttribute = 'cuil';

    protected static ?string $title = 'Relación Activa';

    protected static ?string $modelLabel = 'Relación Activa';

    protected static ?string $pluralModelLabel = 'Relaciones Activas';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal')
                    ->sortable(),
                TextColumn::make('modalidad_contrato')
                    ->sortable(),
                TextColumn::make('fecha_inicio_relacion_laboral')
                    ->date()
                    ->sortable(),
                TextColumn::make('retribucion_pactada')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('modalidad_liquidacion')
                    ->sortable(),
                TextColumn::make('puesto_desem')
                    ->label('Puesto')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
