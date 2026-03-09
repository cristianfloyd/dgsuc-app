<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestarias\AsignacionPresupuestarias;

use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages\CreateAsignacionPresupuestaria;
use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages\EditAsignacionPresupuestaria;
use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages\ListAsignacionPresupuestarias;
use App\Models\Mapuche\Dh24;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AsignacionPresupuestariaResource extends Resource
{
    protected static ?string $model = Dh24::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('codn_area')
                    ->label('Unidad')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $dh24 = new Dh24;
                        $total = $dh24->getTotalAllocationByUnit($state);
                        $set('total_allocated', $total);
                    }),

                TextInput::make('porc_ipres')
                    ->label('Porcentaje')
                    ->numeric()
                    ->rules([
                        'required',
                        'numeric',
                        'min:0',
                        'max:100',
                        function ($attribute, $value, $fail): void {
                            $dh24 = new Dh24;
                            if (! $dh24->isAllocationWithinLimit($value)) {
                                $fail('El porcentaje supera el límite disponible.');
                            }
                        },
                    ]),

                Placeholder::make('total_allocated')
                    ->label('Total Asignado')
                    ->content(fn ($state): string => number_format($state, 2).'%'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codn_area')
                    ->label('Unidad')
                    ->sortable(),
                TextColumn::make('porc_ipres')
                    ->label('Porcentaje')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('codn_area')
                    ->label('Unidad'),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->activo()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAsignacionPresupuestarias::route('/'),
            'create' => CreateAsignacionPresupuestaria::route('/create'),
            'edit' => EditAsignacionPresupuestaria::route('/{record}/edit'),
        ];
    }
}
