<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use App\Models\Dh01;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
use App\Models\Mapuche\MapucheGrupo;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Mapuche\MapucheGrupoLegajo;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\MapucheGrupoResource;
use Filament\Tables\Concerns\InteractsWithTable;

class ManageMapucheGrupoLegajos extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = MapucheGrupoResource::class;

    protected static string $view = 'filament.resources.mapuche-grupo-resource.pages.manage-mapuche-grupo-legajos';

    public MapucheGrupo $record;

    public function table(Table $table): Table
    {
        return $table
            ->query(Dh01::query())
            ->columns([
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->sortable(),

                // Estado en el grupo actual
                Tables\Columns\IconColumn::make('en_grupo_actual')
                    ->label('En Grupo Actual')
                    ->boolean()
                    ->getStateUsing(function (Dh01 $record): bool {
                        return $record->grupos()
                            ->where('mapuche.grupo.id_grupo', $this->record->id_grupo)
                            ->exists();
                    }),

                // Lista de otros grupos
                Tables\Columns\TextColumn::make('otros_grupos')
                    ->label('Otros Grupos')
                    ->getStateUsing(function (Dh01 $record): string {
                        return $record->grupos()
                            ->where('mapuche.grupo.id_grupo', '!=', $this->record->id_grupo)
                            ->pluck('nombre')
                            ->join(', ') ?: 'Sin otros grupos';
                    })
                    ->wrap()
                    ->searchable(false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grupo_actual')
                    ->label('Estado en Grupo Actual')
                    ->options([
                        'en_grupo' => 'En este grupo',
                        'sin_grupo' => 'No en este grupo',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'en_grupo' => $query->whereHas('grupos', function ($q) {
                                $q->where('mapuche.grupo.id_grupo', $this->record->id_grupo);
                            }),
                            'sin_grupo' => $query->whereDoesntHave('grupos', function ($q) {
                                $q->where('mapuche.grupo.id_grupo', $this->record->id_grupo);
                            }),
                            default => $query
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_grupo')
                    ->label(
                        fn(Dh01 $record): string =>
                        $record->grupos()
                            ->where('mapuche.grupo.id_grupo', $this->record->id_grupo)
                            ->exists()
                            ? 'Quitar del Grupo'
                            : 'Agregar al Grupo'
                    )
                    ->icon(
                        fn(Dh01 $record): string =>
                        $record->grupos()
                            ->where('mapuche.grupo.id_grupo', $this->record->id_grupo)
                            ->exists()
                            ? 'heroicon-o-x-mark'
                            : 'heroicon-o-plus'
                    )
                    ->color(
                        fn(Dh01 $record): string =>
                        $record->grupos()
                            ->where('mapuche.grupo.id_grupo', $this->record->id_grupo)
                            ->exists()
                            ? 'danger'
                            : 'success'
                    )
                    ->action(function (Dh01 $record): void {
                        $exists = $record->grupos()
                            ->where('mapuche.grupo.id_grupo', $this->record->id_grupo)
                            ->exists();

                        if ($exists) {
                            MapucheGrupoLegajo::query()
                                ->where('nro_legaj', $record->nro_legaj)
                                ->where('id_grupo', $this->record->id_grupo)
                                ->delete();

                            Notification::make()
                                ->title('Legajo removido del grupo')
                                ->success()
                                ->send();
                        } else {
                            MapucheGrupoLegajo::create([
                                'nro_legaj' => $record->nro_legaj,
                                'id_grupo' => $this->record->id_grupo
                            ]);

                            Notification::make()
                                ->title('Legajo agregado al grupo')
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('agregar_al_grupo')
                    ->label('Agregar al Grupo')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->action(function ($records): void {
                        $records->each(function ($record) {
                            if (!$record->grupos()->where('mapuche.grupo.id_grupo', $this->record->id_grupo)->exists()) {
                                MapucheGrupoLegajo::create([
                                    'nro_legaj' => $record->nro_legaj,
                                    'id_grupo' => $this->record->id_grupo
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('Legajos agregados al grupo')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->visible(fn(): bool => !request()->boolean('tableFilters.en_grupo')),

                Tables\Actions\BulkAction::make('quitar_del_grupo')
                    ->label('Quitar del Grupo')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function ($records): void {
                        $records->each(function ($record) {
                            MapucheGrupoLegajo::query()
                                ->where('nro_legaj', $record->nro_legaj)
                                ->where('id_grupo', $this->record->id_grupo)
                                ->delete();
                        });

                        Notification::make()
                            ->title('Legajos removidos del grupo')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->visible(fn(): bool => request()->boolean('tableFilters.en_grupo')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('asignar_todos')
                    ->label('Asignar Todos los Filtrados')
                    ->icon('heroicon-o-user-plus')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            if (!$this->record->legajos()->where('nro_legaj', $record->nro_legaj)->exists()) {
                                $this->record->legajos()->create(['nro_legaj' => $record->nro_legaj]);
                            }
                        });
                    }),
            ])
            ->defaultSort('nro_legaj', 'asc')
            ->persistSortInSession()
            ->searchable()
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }
}
