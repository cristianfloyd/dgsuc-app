<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AfipMapucheMiSimplificacion;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\RelationManagers;

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
                    ->maxLength(6),
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
                Forms\Components\TextInput::make('tipo_registro')
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
                Forms\Components\TextInput::make('trabajador_agropecuario')
                    ->required()
                    ->maxLength(1)
                    ->default('N'),
                Forms\Components\TextInput::make('modalidad_contrato')
                    ->maxLength(3)
                    ->default('008'),
                Forms\Components\TextInput::make('inicio_rel_laboral')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('fin_rel_laboral')
                    ->maxLength(10),
                Forms\Components\TextInput::make('obra_social')
                    ->maxLength(6)
                    ->default('000000'),
                Forms\Components\TextInput::make('codigo_situacion_baja')
                    ->maxLength(2),
                Forms\Components\TextInput::make('fecha_tel_renuncia')
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
                    ->maxLength(10),
                Forms\Components\TextInput::make('tipo_servicio')
                    ->maxLength(3),
                Forms\Components\TextInput::make('categoria')
                    ->maxLength(6),
                Forms\Components\TextInput::make('fecha_susp_serv_temp')
                    ->maxLength(10),
                Forms\Components\TextInput::make('nro_form_agro')
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
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cuil')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periodo_fiscal')
                    ->sortable(),
                Tables\Columns\IconColumn::make('sino_cerra')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                // Tables\Columns\TextColumn::make('puesto')
                //     ->badge()
                //     ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sino_cerra')
                    ->options([
                        'S' => 'Cerrado',
                        'N' => 'Abierto',
                    ]),
                Tables\Filters\Filter::make('periodo_fiscal')
                    ->form([
                        Forms\Components\TextInput::make('periodo_fiscal')
                            ->mask('999999')
                            ->label('Periodo Fiscal'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('periodo_fiscal', 'desc');
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
