<?php

namespace App\Filament\Mapuche\Resources;

use App\Filament\Mapuche\Resources\AsignacionPresupuestariaResource\Pages;
use App\Models\Mapuche\Dh24;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AsignacionPresupuestariaResource extends Resource
{
    protected static ?string $model = Dh24::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('codn_area')
                    ->label('Unidad')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $dh24 = new Dh24();
                        $total = $dh24->getTotalAllocationByUnit($state);
                        $set('total_allocated', $total);
                    }),

                Forms\Components\TextInput::make('porc_ipres')
                    ->label('Porcentaje')
                    ->numeric()
                    ->rules([
                        'required',
                        'numeric',
                        'min:0',
                        'max:100',
                        function ($attribute, $value, $fail) use ($form): void {
                            $dh24 = new Dh24();
                            if (!$dh24->isAllocationWithinLimit($value)) {
                                $fail('El porcentaje supera el límite disponible.');
                            }
                        },
                    ]),

                Forms\Components\Placeholder::make('total_allocated')
                    ->label('Total Asignado')
                    ->content(fn ($state): string => number_format($state, 2) . '%'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codn_area')
                    ->label('Unidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('porc_ipres')
                    ->label('Porcentaje')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('codn_area')
                    ->label('Unidad'),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->activo()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAsignacionPresupuestarias::route('/'),
            'create' => Pages\CreateAsignacionPresupuestaria::route('/create'),
            'edit' => Pages\EditAsignacionPresupuestaria::route('/{record}/edit'),
        ];
    }
}
