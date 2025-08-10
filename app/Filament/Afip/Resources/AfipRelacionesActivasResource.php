<?php

namespace App\Filament\Afip\Resources;

use App\Filament\Afip\Resources\AfipRelacionesActivasResource\Pages;
use App\Models\AfipRelacionesActivas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AfipRelacionesActivasResource extends Resource
{
    protected static ?string $model = AfipRelacionesActivas::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static ?string $navigationGroup = 'AFIP';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Relación Laboral')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información Principal')
                            ->schema([
                                Forms\Components\TextInput::make('periodo_fiscal')
                                    ->required()
                                    ->length(6)
                                    ->placeholder('YYYYMM'),

                                Forms\Components\TextInput::make('cuil')
                                    ->required()
                                    ->length(11)
                                    ->mask('99-99999999-9')
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('codigo_movimiento')
                                    ->required()
                                    ->options([
                                        '00' => 'Alta',
                                        '01' => 'Baja',
                                        '02' => 'Modificación',
                                    ]),

                                Forms\Components\DatePicker::make('fecha_inicio_relacion_laboral')
                                    ->required()
                                    ->format('Y-m-d'),

                                Forms\Components\DatePicker::make('fecha_fin_relacion_laboral')
                                    ->format('Y-m-d'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Detalles Laborales')
                            ->schema([
                                Forms\Components\TextInput::make('modalidad_contrato')
                                    ->required()
                                    ->length(3),

                                Forms\Components\TextInput::make('codigo_o_social')
                                    ->required()
                                    ->length(6),

                                Forms\Components\TextInput::make('retribucion_pactada')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->maxValue(999999999.99)
                                    ->minValue(0),


                                Forms\Components\Select::make('modalidad_liquidacion')
                                    ->required()
                                    ->options([
                                        'M' => 'Mensual',
                                        'Q' => 'Quincenal',
                                        'S' => 'Semanal',
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Información Adicional')
                            ->schema([
                                Forms\Components\TextInput::make('suc_domicilio_desem')
                                    ->required()
                                    ->length(5),

                                Forms\Components\TextInput::make('actividad_domicilio_desem')
                                    ->required()
                                    ->length(6),

                                Forms\Components\TextInput::make('puesto_desem')
                                    ->required()
                                    ->length(4),

                                Forms\Components\TextInput::make('ccct')
                                    ->required()
                                    ->length(7),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodo_fiscal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cuil')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string =>
                    substr($state, 0, 2) . '-' .
                        substr($state, 2, 8) . '-' .
                        substr($state, -1)),

                Tables\Columns\TextColumn::make('codigo_movimiento')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string =>
                    match ($state) {
                        '00' => 'success',
                        '01' => 'danger',
                        '02' => 'warning',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('fecha_inicio_relacion_laboral')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('retribucion_pactada')
                    ->money('ARS')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('codigo_movimiento')
                    ->options([
                        '00' => 'Alta',
                        '01' => 'Modificación',
                        '02' => 'Baja',
                    ]),

                Tables\Filters\Filter::make('fecha_inicio')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_inicio_relacion_laboral', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_inicio_relacion_laboral', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAfipRelacionesActivas::route('/'),
            'create' => Pages\CreateAfipRelacionesActivas::route('/create'),
            'edit' => Pages\EditAfipRelacionesActivas::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['cuil', 'periodo_fiscal'];
    }
}
