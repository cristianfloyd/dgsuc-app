<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AfipMapucheMiSimplificacionResource extends Resource
{
    protected static ?string $model = AfipMapucheMiSimplificacion::class;
    protected static ?string $navigationLabel = 'Mi Simplificacion';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected  static ?string $navigationGroup = 'Afip';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAfipMapucheMiSimplificacions::route('/'),
            // 'create' => Pages\CreateAfipMapucheMiSimplificacion::route('/create'),
            // 'edit' => Pages\EditAfipMapucheMiSimplificacion::route('/{record}/edit'),
        ];
    }
}
