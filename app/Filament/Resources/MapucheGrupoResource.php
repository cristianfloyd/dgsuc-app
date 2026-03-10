<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapucheGrupoResource\Pages\CreateMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\Pages\EditMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\Pages\ListMapucheGrupos;
use App\Filament\Resources\MapucheGrupoResource\Pages\ManageMapucheGrupoLegajos;
use App\Filament\Resources\MapucheGrupoResource\Pages\ViewMapucheGrupo;
use App\Filament\Resources\MapucheGrupoResource\RelationManagers\LegajosRelationManager;
use App\Models\Mapuche\MapucheGrupo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MapucheGrupoResource extends Resource
{
    protected static ?string $model = MapucheGrupo::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | UnitEnum | null $navigationGroup = 'Liquidaciones';

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(30)
                    ->label('Nombre del Grupo'),

                TextInput::make('tipo')
                    ->required()
                    ->maxLength(20)
                    ->label('Tipo'),

                Textarea::make('descripcion')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->label('Descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),

                TextColumn::make('tipo')
                    ->searchable()
                    ->sortable()
                    ->label('Tipo'),

                TextColumn::make('descripcion')
                    ->searchable()
                    ->limit(50)
                    ->label('Descripción'),

                TextColumn::make('legajos_count')
                    ->counts('legajos')
                    ->label('Cantidad de Legajos'),

                TextColumn::make('fec_modificacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Última Modificación'),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options(fn () => MapucheGrupo::distinct()->pluck('tipo', 'tipo')->toArray())
                    ->label('Filtrar por Tipo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('administrar_legajos')
                    ->label('Administrar Legajos')
                    ->icon('heroicon-o-users')
                    ->url(fn (MapucheGrupo $record): string => static::getUrl('manage-legajos', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LegajosRelationManager::class,
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
