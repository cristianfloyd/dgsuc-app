<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AfipMapucheMiSimplificacionResource extends Resource
{
    protected static ?string $model = AfipMapucheMiSimplificacion::class;
    protected static ?string $navigationLabel = 'Mi Simplificacion';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected  static ?string $navigationGroup = 'Afip';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id'),
                TextColumn::make('nro_legaj')->numeric()->sortable()->searchable(),
                TextColumn::make('nro_liqui')->numeric()->sortable(),
                TextColumn::make('sino_cerra')->searchable(),
                TextColumn::make('desc_estado_liquidacion')->searchable(),
                TextColumn::make('nro_cargo')->numeric()->sortable()->searchable(),
                TextColumn::make('periodo_fiscal'),
                TextColumn::make('tipo_de_registro'),
                TextColumn::make('codigo_movimiento'),
                TextColumn::make('cuil')->sortable()->searchable(),
                TextColumn::make('marca_de_trabajador_agropecuario'),
                TextColumn::make('modalidad_de_contrato'),
                TextColumn::make('inicio_rel_laboral'),
                TextColumn::make('fin_rel_laboral'),
                TextColumn::make('obra_social'),
                TextColumn::make('codigo_situacion_baja'),
                TextColumn::make('fecha_telegrama_renuncia'),
                TextColumn::make('retribucion_pactada'),
                TextColumn::make('modalidad_liquidacion'),
                TextColumn::make('domicilio'),
                TextColumn::make('actividad'),
                TextColumn::make('puesto'),
                TextColumn::make('rectificacion'),
                TextColumn::make('ccct'),
                TextColumn::make('tipo_servicio'),
                TextColumn::make('categoria'),
                TextColumn::make('fecha_susp_serv_temp'),
                TextColumn::make('nro_form_agropecuario'),
                TextColumn::make('covid'),
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
