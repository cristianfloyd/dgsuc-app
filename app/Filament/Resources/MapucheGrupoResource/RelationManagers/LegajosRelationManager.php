<?php

namespace App\Filament\Resources\MapucheGrupoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class LegajosRelationManager extends RelationManager
{
    protected static string $relationship = 'legajos';

    protected static ?string $title = 'Legajos del Grupo';

    protected static ?string $modelLabel = 'legajo';

    protected static ?string $pluralModelLabel = 'legajos';

    protected static ?string $recordTitleAttribute = 'desc_appat';

    public function getTableRecordTitle(?Model $record): string
    {
        return "{$record->nro_legaj} - {$record->desc_appat}, {$record->desc_nombr}";
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nro_legaj')
            ->columns([
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('dh01.nro_legaj', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dh01.nro_legaj', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('desc_appat')
                    ->label('Apellido')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('dh01.desc_appat', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dh01.desc_appat', 'ilike', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('desc_nombr')
                    ->label('Nombre')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('dh01.desc_nombr', $direction);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dh01.desc_nombr', 'ilike', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($query) use ($search) {
                            // Eliminar cualquier caracter no numérico del término de búsqueda
                            $search = preg_replace('/[^0-9]/', '', $search);

                            // Búsqueda por CUIL completo usando la concatenación de los campos
                            return $query->whereRaw("LPAD(nro_cuil1::text, 2, '0') || LPAD(nro_cuil::text, 8, '0') || LPAD(nro_cuil2::text, 1, '0') LIKE ?", ["%{$search}%"]);
                        });
                    })
                    ->formatStateUsing(
                        fn($state) =>
                        // Formatear el CUIL con guiones (XX-XXXXXXXX-X)
                        preg_replace('/^(\d{2})(\d{8})(\d{1})$/', '$1-$2-$3', $state)
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('administrar')
                    ->label('Administrar Legajos')
                    ->icon('heroicon-m-users')
                    ->url(
                        fn(RelationManager $livewire): string =>
                        route('filament.dashboard.resources.mapuche-grupos.manage-legajos', [
                            'record' => $livewire->getOwnerRecord(),
                        ])
                    ),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Quitar del Grupo')
                    ->modalHeading('Quitar Legajo del Grupo')
                    ->modalDescription('¿Está seguro que desea quitar este legajo del grupo?')
                    ->successNotificationTitle('Legajo quitado del grupo'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Quitar Seleccionados')
                        ->modalDescription('¿Está seguro que desea quitar estos legajos del grupo?')
                        ->successNotificationTitle('Legajos quitados del grupo'),
                ]),
            ])
            ->defaultSort('dh01.nro_legaj', 'asc')
            ->persistSortInSession()
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
