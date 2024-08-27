<?php

namespace App\Filament\Resources;

use App\Models\Dh11;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Dh11Resource\Pages;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;

class Dh11Resource extends Resource
{
    protected static ?string $model = Dh11::class;
    protected static ?string $modelLabel = 'Categorias';
    protected static ?string $navigationLabel = 'Categorias';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('impp_basic')
                    ->numeric()
                    //->reactive()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state) {
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
                //TextInput::make('equivalencia')
                //->maxLength(3),
                TextInput::make('tipo_escal')
                    ->maxLength(1),
                //TextInput::make('nro_escal')
                //->numeric(),
                // Forms\Components\TextInput::make('sino_mensu')
                // ->maxLength(1),
                // Forms\Components\TextInput::make('sino_djpat')
                // ->maxLength(1),
                // Forms\Components\TextInput::make('vig_caano')
                //     ->numeric(),
                // Forms\Components\TextInput::make('vig_cames')
                //     ->numeric(),
                // Forms\Components\TextInput::make('desc_categ')
                //     ->maxLength(20),
                // Forms\Components\TextInput::make('sino_jefat')
                //     ->maxLength(1),
                // Forms\Components\TextInput::make('computaantig')
                //     ->numeric(),
                // Forms\Components\Toggle::make('controlcargos'),
                // Forms\Components\Toggle::make('controlhoras'),
                // Forms\Components\Toggle::make('controlpuntos'),
                // Forms\Components\Toggle::make('controlpresup'),
                // Forms\Components\TextInput::make('horasmenanual')
                // ->maxLength(1),
                // Forms\Components\TextInput::make('cantpuntos')
                //     ->numeric(),
                // Forms\Components\TextInput::make('estadolaboral')
                //     ->maxLength(1),
                // Forms\Components\TextInput::make('nivel')
                //     ->maxLength(3),
                // Forms\Components\TextInput::make('tipocargo')
                //     ->maxLength(30),
                // Forms\Components\TextInput::make('remunbonif')
                //     ->numeric(),
                // Forms\Components\TextInput::make('noremunbonif')
                //     ->numeric(),
                // Forms\Components\TextInput::make('remunnobonif')
                //     ->numeric(),
                // Forms\Components\TextInput::make('noremunnobonif')
                //     ->numeric(),
                // Forms\Components\TextInput::make('otrasrem')
                //     ->numeric(),
                // Forms\Components\TextInput::make('dto1610')
                //     ->numeric(),
                // Forms\Components\TextInput::make('reflaboral')
                //     ->numeric(),
                // Forms\Components\TextInput::make('refadm95')
                //     ->numeric(),
                // Forms\Components\TextInput::make('critico')
                //     ->numeric(),
                // Forms\Components\TextInput::make('jefatura')
                //     ->numeric(),
                // Forms\Components\TextInput::make('gastosrepre')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codigoescalafon')
                //     ->maxLength(4),
                // Forms\Components\TextInput::make('noinformasipuver')
                //     ->numeric(),
                // Forms\Components\TextInput::make('noinformasirhu')
                //     ->numeric()
                //     ->default(0),
                // Forms\Components\TextInput::make('imppnooblig')
                //     ->numeric(),
                // Forms\Components\Toggle::make('aportalao'),
                // Forms\Components\TextInput::make('factor_hs_catedra')
                //     ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codc_categ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('codc_dedic')->label('Código')->toggleable()->sortable(),
                TextColumn::make('desc_categ')->label('Descripción Categoría')->searchable()->sortable(),
                TextColumn::make('dh89.descesc')->label('Escalafón')->toggleable(),
                TextColumn::make('nro_escal')->label('Número Escalafón')->toggleable(),
                // TextInputColumn::make('impp_basic')->label('Importe Básico')->sortable(),
                TextColumn::make('impp_basic')->label('Importe Básico')->sortable(),
                // TextInputColumn::make('impp_asign')->label('Importe Asignación')->sortable(),
                TextColumn::make('impp_asign')->label('Importe Asignación')->sortable(),
                TextColumn::make('estadolaboral')->label('est lab')->sortable(),
                //llamar a la tabla dh31
                TextColumn::make('dh31.desc_dedic')->label('Dedicación')->toggleable(),
                ToggleColumn::make('sino_mensu')->label('Mensualizado')->toggleable(),
                ToggleColumn::make('sino_djpat')->label('Declaración Jurada Patrimonial')->toggleable(),
                TextColumn::make('vig_caano')->label('Vigencia Año')->toggleable(),
                TextColumn::make('vig_cames')->label('Vigencia Mes')->toggleable(),
                ToggleColumn::make('controlcargos')->label('Control Cargos')->toggleable(),
                ToggleColumn::make('controlhoras')->label('Control Horas')->toggleable(),
                ToggleColumn::make('controlpuntos')->label('Control Puntos')->toggleable(),
                ToggleColumn::make('controlpresup')->label('Control Presupuesto')->toggleable(),
                TextColumn::make('nivel')->label('Nivel'),
                // TextColumn::make('tipocargo')->label('Tipo de Cargo'),
                // TextColumn::make('remunbonif')->label('Remuneración Bonificada'),
                // TextColumn::make('noremunbonif')->label('No Remuneración Bonificada'),
                // TextColumn::make('remunnobonif')->label('Remuneración No Bonificada'),
                // TextColumn::make('noremunnobonif')->label('No Remuneración No Bonificada'),
                // TextColumn::make('otrasrem')->label('Otras Remuneraciones'),
            ])
            ->filters([
                SelectFilter::make('tipo_escal')
                    ->options([
                        'D' => 'Docente',
                        'N' => 'NoDo',
                        'S' => 'Superior',
                    ]),
                SelectFilter::make('estadolaboral')
                    ->options([
                        'A' => 'Ad',
                        'B' => 'Baja',
                        'P' => 'P',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modal(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
            //'edit' => Pages\EditDh11::route('/{record}/edit'),
        ];
    }
    public static function getWidgets(): array
    {
        return [
            ActualizarImppBasicWidget::class,
        ];
    }
}
