<?php

namespace App\Filament\Resources;

use App\Models\Dh11;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use App\Traits\CategoriasConstantTrait;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\Dh11Resource\Pages;

class Dh11Resource extends Resource
{
    use CategoriasConstantTrait;
    protected static ?string $model = Dh11::class;
    protected static ?string $modelLabel = 'Básicos (dh11)';
    protected static ?string $navigationLabel = 'Básicos (dh11)';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('impp_basic')
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?float $state) {
                        $set('impp_asign', $state);
                        //fn (Set $set, ?int $state) => $set('impp_asign', $state)
                    }),
                TextInput::make('impp_asign')
                    ->numeric(),
                TextInput::make('codc_categ')
                    ->required()
                    ->maxLength(4),
                TextInput::make('codc_dedic')
                    ->maxLength(4),
                TextInput::make('tipo_escal')
                    ->maxLength(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('codigoescalafon')
            ->groups([
                Group::make('codigoescalafon')
                    ->label('Escalafón')
                    ->collapsible()
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->columns([
                TextColumn::make('codc_categ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('codc_dedic')->label('Código')->toggleable()->sortable(),
                TextColumn::make('dh31.desc_dedic')->label('Dedicación')->toggleable(),
                TextColumn::make('desc_categ')->label('Descripción Categoría')->searchable()->sortable(),
                TextColumn::make('dh89.descesc')->label('Escalafón')->toggleable(),
                TextColumn::make('nro_escal')->label('Número Escalafón')->toggleable()->toggledHiddenByDefault(),
                // TextColumn::make('impp_basic')->label('Importe Básico')->sortable(),
                TextInputColumn::make('impp_basic')->label('Importe Básico')->sortable()
                    ->rules([
                        'numeric',
                        'min:0',
                        'max:1000000000',
                    ]),
                TextColumn::make('impp_asign')->label('Importe Asignación')->numeric()->disabled(),
                TextColumn::make('estadolaboral')->label('est lab')->toggleable(isToggledHiddenByDefault:true),
                //llamar a la tabla dh31
                ToggleColumn::make('sino_mensu')->label('Mensualizado')->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('vig_caano')->label('Vigencia Año')->toggleable(),
                TextColumn::make('vig_cames')->label('Vigencia Mes')->toggleable(),
                ToggleColumn::make('controlcargos')->label('Control Cargos')->toggleable(isToggledHiddenByDefault:true),
                ToggleColumn::make('controlhoras')->label('Control Horas')->toggleable(isToggledHiddenByDefault:true),
                ToggleColumn::make('controlpuntos')->label('Control Puntos')->toggleable(isToggledHiddenByDefault:true),
                ToggleColumn::make('controlpresup')->label('Control Presupuesto')->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('nivel')->label('Nivel')->toggleable(isToggledHiddenByDefault:true),
            ])
            ->filters([
                SelectFilter::make('escalafon')
                    ->options([
                        'DOC2' => 'Preuniversitario',
                        'DOCU' => 'Docente Universitario',
                        'AUTU' => 'Autoridad Universitaria',
                        'NODO' => 'Nodocente'
                    ])
                    ->query(function (Builder $query, array $data){
                        if (!$data['value']){
                            return $query;
                        }

                        $categorias = match ($data['value']) {
                            'DOCU' => self::CATEGORIAS['DOCU'],
                            'DOCS' => self::CATEGORIAS['DOCS'],
                            'DOC2' => self::CATEGORIAS['DOC2'],
                            'AUTU' => self::CATEGORIAS['AUTU'],
                            'AUTS' => self::CATEGORIAS['AUTS'],
                            'NODO' => self::CATEGORIAS['NODO'],
                            default => [],
                        };

                        return $query->whereIn('codc_categ', $categorias);
                    }),
                SelectFilter::make('estadolaboral')
                    ->options([
                        'A' => 'Ad',
                        'B' => 'Baja',
                        'P' => 'P',
                    ])
                    ->default('P')
            ])
            ->actions([
                //Tables\Actions\EditAction::make()->modal(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('codc_categ', 'asc');
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
            'index' => Pages\ListDh11s::route('/'),
            'create' => Pages\CreateDh11::route('/create'),
            'edit' => Pages\EditDh11::route('/{record}/edit'),
        ];
    }
    public static function getWidgets(): array
    {
        return [
        ];
    }
}
