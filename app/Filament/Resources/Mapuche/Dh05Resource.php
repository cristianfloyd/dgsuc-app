<?php

namespace App\Filament\Resources\Mapuche;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh05;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\Mapuche\Dh05Resource\Pages;
use App\Filament\Resources\Mapuche\Dh05Resource\RelationManagers;

class Dh05Resource extends Resource
{
    protected static ?string $model = Dh05::class;
    protected static ?string $label = 'Licencia de empleado/cargo';
    protected static ?string $pluralLabel = 'Licencias de empleados/cargos';
    protected static ?string $navigationLabel = 'Licencias';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nro_legaj')
                    ->numeric(),
                TextInput::make('nro_cargo')
                    ->numeric(),
                DatePicker::make('fec_desde')
                    ->required(),
                DatePicker::make('fec_hasta'),
                DatePicker::make('fecha_finalorig'),
                TextInput::make('nrovarlicencia')
                    ->numeric(),
                TextInput::make('observacion')
                    ->maxLength(255),
                TextInput::make('tipo_norma_alta')
                    ->maxLength(20),
                TextInput::make('emite_norma_alta')
                    ->maxLength(20),
                DatePicker::make('fecha_norma_alta'),
                TextInput::make('nro_norma_alta')
                    ->numeric(),
                TextInput::make('tipo_norma_baja')
                    ->maxLength(20),
                TextInput::make('emite_norma_baja')
                    ->maxLength(20),
                DatePicker::make('fecha_norma_baja'),
                TextInput::make('nro_norma_baja')
                    ->numeric(),
                TextInput::make('mes_actualizacion')
                    ->numeric(),
                TextInput::make('anio_actualizacion')
                    ->numeric(),
                TextInput::make('trab_sab')
                    ->numeric(),
                TextInput::make('trab_dom')
                    ->numeric(),
                TextInput::make('presentismo')
                    ->numeric(),
                TextInput::make('codmotivolic')
                    ->numeric(),
                TextInput::make('trab_fer')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_licencia')->numeric()->sortable(),
                TextColumn::make('nro_legaj')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fec_desde')
                    ->date()
                    ->sortable(),
                TextColumn::make('fec_hasta')
                    ->date()
                    ->sortable(),
                TextColumn::make('fecha_finalorig')
                    ->date()
                    ->sortable(),
                TextColumn::make('nrovarlicencia')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('observacion')
                    ->searchable(),
                TextColumn::make('tipo_norma_alta')
                    ->searchable(),
                TextColumn::make('emite_norma_alta')
                    ->searchable(),
                TextColumn::make('fecha_norma_alta')
                    ->date()
                    ->sortable(),
                TextColumn::make('nro_norma_alta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_norma_baja')
                    ->searchable(),
                TextColumn::make('emite_norma_baja')
                    ->searchable(),
                TextColumn::make('fecha_norma_baja')
                    ->date()
                    ->sortable(),
                TextColumn::make('nro_norma_baja')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mes_actualizacion')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('anio_actualizacion')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trab_sab')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trab_dom')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('presentismo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codmotivolic')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trab_fer')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fec_desde', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5, 10, 25, 50, 100, 250])
            ->searchable();;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDh05s::route('/'),
            'create' => Pages\CreateDh05::route('/create'),
            'edit' => Pages\EditDh05::route('/{record}/edit'),
        ];
    }
}
