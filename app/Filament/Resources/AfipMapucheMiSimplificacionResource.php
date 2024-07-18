<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Filament\Resources\AfipMapucheMiSimplificacionResource\RelationManagers;
use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AfipMapucheMiSimplificacionResource extends Resource
{
    protected static ?string $model = AfipMapucheMiSimplificacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nro_legaj')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nro_liqui')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sino_cerra')
                    ->required()
                    ->maxLength(1),
                Forms\Components\TextInput::make('desc_estado_liquidacion')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('nro_cargo')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('periodo_fiscal')
                    ->required()
                    ->maxLength(6),
                Forms\Components\TextInput::make('tipo_de_registro')
                    ->required()
                    ->maxLength(2)
                    ->default(01),
                Forms\Components\TextInput::make('codigo_movimiento')
                    ->required()
                    ->maxLength(2)
                    ->default('AT'),
                Forms\Components\TextInput::make('cuil')
                    ->required()
                    ->maxLength(11),
                Forms\Components\TextInput::make('marca_de_trabajador_agropecuario')
                    ->required()
                    ->maxLength(1)
                    ->default('N'),
                Forms\Components\TextInput::make('modalidad_de_contrato'),
                    // ->maxLength(3)
                    // ->default(008),
                Forms\Components\TextInput::make('inicio_rel_laboral')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('fin_rel_laboral')
                    ->maxLength(10),
                Forms\Components\TextInput::make('obra_social')
                    ->maxLength(6)
                    ->default(000000),
                Forms\Components\TextInput::make('codigo_situacion_baja')
                    ->maxLength(2),
                Forms\Components\TextInput::make('fecha_tel_renuncia')
                    ->tel()
                    ->maxLength(10),
                Forms\Components\TextInput::make('retribucion_pactada')
                    ->maxLength(15),
                Forms\Components\TextInput::make('modalidad_liquidacion')
                    ->required()
                    ->maxLength(1)
                    ->default(1),
                Forms\Components\TextInput::make('domicilio')
                    ->maxLength(5),
                Forms\Components\TextInput::make('actividad')
                    ->maxLength(6),
                Forms\Components\TextInput::make('puesto')
                    ->maxLength(4),
                Forms\Components\TextInput::make('rectificacion')
                    ->maxLength(2),
                Forms\Components\TextInput::make('ccct')
                    ->maxLength(10)
                    ->default(0000000000),
                Forms\Components\TextInput::make('tipo_servicio')
                    ->maxLength(3),
                Forms\Components\TextInput::make('categoria')
                    ->maxLength(6),
                Forms\Components\TextInput::make('fecha_susp_serv_temp')
                    ->maxLength(10),
                Forms\Components\TextInput::make('nro_form_agropecuario')
                    ->maxLength(10),
                Forms\Components\TextInput::make('covid')
                    ->maxLength(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sino_cerra')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc_estado_liquidacion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periodo_fiscal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_de_registro')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo_movimiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cuil')
                    ->searchable(),
                Tables\Columns\TextColumn::make('marca_de_trabajador_agropecuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('modalidad_de_contrato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inicio_rel_laboral')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fin_rel_laboral')
                    ->searchable(),
                Tables\Columns\TextColumn::make('obra_social')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo_situacion_baja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_telegrama_renuncia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('retribucion_pactada')
                    ->searchable(),
                Tables\Columns\TextColumn::make('modalidad_liquidacion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domicilio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('actividad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('puesto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rectificacion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ccct')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_servicio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_susp_serv_temp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_form_agropecuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('covid')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListAfipMapucheMiSimplificacions::route('/'),
            'create' => Pages\CreateAfipMapucheMiSimplificacion::route('/create'),
            'edit' => Pages\EditAfipMapucheMiSimplificacion::route('/{record}/edit'),
        ];
    }
}
