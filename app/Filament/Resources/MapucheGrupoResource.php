<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Models\Mapuche\MapucheGrupo;
use Filament\Resources\RelationManagers;
use App\Filament\Resources\MapucheGrupoResource\Pages\EditMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\Pages\ViewMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\Pages\ListMapucheGrupos;
use App\Filament\Resources\MapucheGrupoResource\Pages\CreateMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\Pages\ManageMapucheGrupoLegajos;
use App\Filament\Resources\MapucheGrupoResource\RelationManagers\LegajosRelationManager;

class MapucheGrupoResource extends Resource
{
    protected static ?string $model = MapucheGrupo::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Liquidaciones';

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(30)
                    ->label('Nombre del Grupo'),

                Forms\Components\TextInput::make('tipo')
                    ->required()
                    ->maxLength(20)
                    ->label('Tipo'),

                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->label('Descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),

                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->sortable()
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable()
                    ->limit(50)
                    ->label('Descripción'),

                Tables\Columns\TextColumn::make('legajos_count')
                    ->counts('legajos')
                    ->label('Cantidad de Legajos'),

                Tables\Columns\TextColumn::make('fec_modificacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Última Modificación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(fn() => MapucheGrupo::distinct()->pluck('tipo', 'tipo')->toArray())
                    ->label('Filtrar por Tipo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('administrar_legajos')
                    ->label('Administrar Legajos')
                    ->icon('heroicon-o-users')
                    ->url(fn(MapucheGrupo $record): string => static::getUrl('manage-legajos', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LegajosRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMapucheGrupos::route('/'),
            'create' => CreateMapucheGrupo::route('/create'),
            'view' => ViewMapucheGrupo::route('/{record}'),
            'edit' => EditMapucheGrupo::route('/{record}/edit'),
            'manage-legajos' => ManageMapucheGrupoLegajos::route('/{record}/legajos'),
        ];
    }
}
