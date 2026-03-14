<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivas\AfipRelacionesActivas;

use App\Filament\Afip\Resources\AfipRelacionesActivas\Pages\CreateAfipRelacionesActivas;
use App\Filament\Afip\Resources\AfipRelacionesActivas\Pages\EditAfipRelacionesActivas;
use App\Filament\Afip\Resources\AfipRelacionesActivas\Pages\ListAfipRelacionesActivas;
use App\Models\AfipRelacionesActivas;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;
use UnitEnum;

class AfipRelacionesActivasResource extends Resource
{
    protected static ?string $model = AfipRelacionesActivas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static string|UnitEnum|null $navigationGroup = 'AFIP';

    protected static ?int $navigationSort = 1;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Relación Laboral')
                    ->tabs([
                        Tab::make('Información Principal')
                            ->schema([
                                TextInput::make('periodo_fiscal')
                                    ->required()
                                    ->length(6)
                                    ->placeholder('YYYYMM'),

                                TextInput::make('cuil')
                                    ->required()
                                    ->length(11)
                                    ->mask('99-99999999-9')
                                    ->unique(ignoreRecord: true),

                                Select::make('codigo_movimiento')
                                    ->required()
                                    ->options([
                                        '00' => 'Alta',
                                        '01' => 'Baja',
                                        '02' => 'Modificación',
                                    ]),

                                DatePicker::make('fecha_inicio_relacion_laboral')
                                    ->required()
                                    ->format('Y-m-d'),

                                DatePicker::make('fecha_fin_relacion_laboral')
                                    ->format('Y-m-d'),
                            ]),

                        Tab::make('Detalles Laborales')
                            ->schema([
                                TextInput::make('modalidad_contrato')
                                    ->required()
                                    ->length(3),

                                TextInput::make('codigo_o_social')
                                    ->required()
                                    ->length(6),

                                TextInput::make('retribucion_pactada')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->maxValue(999999999.99)
                                    ->minValue(0),

                                Select::make('modalidad_liquidacion')
                                    ->required()
                                    ->options([
                                        'M' => 'Mensual',
                                        'Q' => 'Quincenal',
                                        'S' => 'Semanal',
                                    ]),
                            ]),

                        Tab::make('Información Adicional')
                            ->schema([
                                TextInput::make('suc_domicilio_desem')
                                    ->required()
                                    ->length(5),

                                TextInput::make('actividad_domicilio_desem')
                                    ->required()
                                    ->length(6),

                                TextInput::make('puesto_desem')
                                    ->required()
                                    ->length(4),

                                TextInput::make('ccct')
                                    ->required()
                                    ->length(7),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cuil')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => substr($state, 0, 2) . '-'
                        . substr($state, 2, 8) . '-'
                        . substr($state, -1)),

                TextColumn::make('codigo_movimiento')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '00' => 'success',
                        '01' => 'danger',
                        '02' => 'warning',
                        default => 'secondary',
                    }),

                TextColumn::make('fecha_inicio_relacion_laboral')
                    ->date()
                    ->sortable(),

                TextColumn::make('retribucion_pactada')
                    ->money('ARS')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('codigo_movimiento')
                    ->options([
                        '00' => 'Alta',
                        '01' => 'Modificación',
                        '02' => 'Baja',
                    ]),

                Filter::make('fecha_inicio')
                    ->schema([
                        DatePicker::make('desde'),
                        DatePicker::make('hasta'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when(
                            $data['desde'],
                            fn(Builder $query, $date): Builder => $query->whereDate('fecha_inicio_relacion_laboral', '>=', $date),
                        )
                        ->when(
                            $data['hasta'],
                            fn(Builder $query, $date): Builder => $query->whereDate('fecha_inicio_relacion_laboral', '<=', $date),
                        )),
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(5);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [

        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAfipRelacionesActivas::route('/'),
            'create' => CreateAfipRelacionesActivas::route('/create'),
            'edit' => EditAfipRelacionesActivas::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['cuil', 'periodo_fiscal'];
    }
}
