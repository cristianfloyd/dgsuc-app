<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Dh03;
use App\Models\Dhc9;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Dh03Resource\Pages;

class Dh03Resource extends Resource
{
    protected static ?string $model = Dh03::class;
    protected static ?string $modelLabel = 'Cargos';
    protected static ?string $navigationLabel = 'Cargos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nro_cargo')->numeric()->required(),
                Forms\Components\TextInput::make('nro_legaj')->numeric()->required(),
                Forms\Components\DatePicker::make('fec_alta')->required(),
                Forms\Components\DatePicker::make('fec_baja')->required(),
                Forms\Components\TextInput::make('codc_carac')->maxLength(4)->required(),
                Forms\Components\TextInput::make('codc_categ')->maxLength(4)->required(),
                Forms\Components\TextInput::make('codc_agrup')->maxLength(4)->required(),
                Forms\Components\TextInput::make('tipo_norma')->maxLength(20)->required(),
                Forms\Components\TextInput::make('codc_uacad')->maxLength(4)->required(),
                Forms\Components\TextInput::make('coddependesemp')->maxLength(4)->required(),
                Forms\Components\TextInput::make('porc_aplic')->numeric()->required(),
                Forms\Components\TextInput::make('hs_dedic')->numeric()->required(),
                Forms\Components\DatePicker::make('fecha_norma_baja')->required(),
                Forms\Components\DatePicker::make('fechapermanencia')->required(),
                Forms\Components\DatePicker::make('fecaltadesig')->required(),
                Forms\Components\DatePicker::make('fecbajadesig')->required(),
                Forms\Components\TextInput::make('motivobajadesig')->numeric()->required(),
                Forms\Components\Toggle::make('chkstopliq')->required(),
                Forms\Components\TextInput::make('cod_clasif_cargo')->numeric()->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('nro_cargo')->label('Cargo')->numeric()->sortable(),
            TextColumn::make('nro_legaj')->label('Legajo')->numeric()->sortable()->searchable(),
            TextColumn::make('fec_alta')->date()->sortable()->toggleable()->toggledHiddenByDefault(),
            TextColumn::make('fec_baja')->date()->sortable()->toggleable()->toggledHiddenByDefault(),
            TextColumn::make('codc_carac')->label('Caracter Escalafon')->toggleable()->toggledHiddenByDefault(),
            TextColumn::make('codc_categ')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('dh11.desc_categ')->label('Categoria')->sortable()->toggleable(),
            TextColumn::make('codc_agrup')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('dhc9.descagrup')->label('Agrupacion')->sortable()->toggleable(),
                TextColumn::make('tipo_norma')->toggleable()->toggledHiddenByDefault(),
            TextColumn::make('codc_uacad')->label('Dependencia')->sortable()->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('dh30.desc_item')->label('Dependencia')->sortable()->toggleable(),
            TextColumn::make('dh36.descdependesemp')->label('Dep. des.')->sortable()->toggleable(),
            TextColumn::make('porc_aplic')->numeric()->sortable(),
            TextColumn::make('hs_dedic')->numeric()->sortable(),
            TextColumn::make('fecha_norma_baja')->date()->sortable(),
            TextColumn::make('fechapermanencia')->date()->sortable(),
            TextColumn::make('fecaltadesig')->date()->sortable(),
            TextColumn::make('fecbajadesig')->date()->sortable(),
            TextColumn::make('motivobajadesig')->numeric()->sortable(),
            IconColumn::make('chkstopliq')->boolean(),
            TextColumn::make('cod_clasif_cargo')->numeric()->sortable(),
        ])
            ->filters([
                // SelectFilter::make('dh11.de_categ')->label('Categoria')
                //     ->searchable()
                //     ->preload()
                //     ->relationship( 'dh11',  'desc_categ')
                //     ->options(Dh11::all()->pluck('desc_categ', 'codc_categ')->toArray()),
                SelectFilter::make('codc_agrup')
                    ->label('Agrupacion')
                    ->relationship('dhc9', 'descagrup')
                    ->options(Dhc9::all()->pluck('descagrup', 'codc_agrup')->toArray()),
                Filter::make('estado_cargo')
                    ->label('Estado del Cargo')
                    ->form([
                        Select::make('estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                'todos' => 'Todos',
                            ])
                            ->default('todos')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['estado']) {
                            'activo' => $query->whereNull('fec_baja')->orWhere('fec_baja', '>=', now()),
                            'inactivo' => $query->whereNotNull('fec_baja')->where('fec_baja', '<', now()),
                            default => $query,
                        };
                    }),
                SelectFilter::make('codc_uacad')->label('Dependencia')
                    ->relationship('dh30', 'desc_item')
                    ->searchable()
                    ->preload(),
                Filter::make('chkstopliq')
                    ->label('Stop Liquidacion')
                    ->toggle()
                    ->query(fn ($query) => $query->where('chkstopliq', false))
                    ->default(true), // Aplicar el filtro por defecto
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nro_cargo', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5,10,25,50,100, 250, 500, 1000])
            // ->headerActions([
            //     Action::make('filterByDescCateg')
            //         ->label('Filtrar por Categoría')
            //         ->form([
            //             Select::make('desc_categ')
            //                 ->label('Categoría')
            //                 ->relationship('dh11', 'desc_categ')
            //                 ->searchable()
            //                 ->preload()
            //                 ->required()
            //                 ->getSearchResultsUsing(fn (string $search) => Dh11::where('desc_categ', 'like', "%{$search}%")
            //                     ->limit(20)
            //                     ->pluck('desc_categ', 'codc_categ'))
            //                 ->getOptionLabelUsing(fn ($value) => Dh11::find($value)?->codc_categ ?? 'N/A'),
            //         ])
            //         ->action(function (array $data, Table $table) {
            //             // dump(Dh11::where('codc_categ', $data['desc_categ'])->get());
            //             if (!empty($data['desc_categ']) ){
            //                 $table->query(Dh03::where('codc_categ', $data['desc_categ']));
            //             }
            //         }),
            // ])
            ;
    }

/**
     * Define the relations for the resource.
     */
    public static function getRelations(): array
    {
        return [
            // Define relations if any
        ];
    }

    /**
     * Define the pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDh03s::route('/'),
            'create' => Pages\CreateDh03::route('/create'),
            'edit' => Pages\EditDh03::route('/{record}/edit'),
        ];
    }
}
