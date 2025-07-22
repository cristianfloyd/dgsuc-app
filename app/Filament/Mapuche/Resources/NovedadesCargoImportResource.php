<?php

namespace App\Filament\Mapuche\Resources;

use App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;
use App\Models\NovedadesCargoImportModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NovedadesCargoImportResource extends Resource
{
    // -------------------------------------------------------------------------
    // Nombre del modelo Eloquent relacionado (podría ser temporal o definitivo)
    // -------------------------------------------------------------------------
    protected static ?string $model = NovedadesCargoImportModel::class;

    // -------------------------------------------------------------------------
    // Configuración del icono y del label
    // -------------------------------------------------------------------------
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Importar Cargos';

    // -------------------------------------------------------------------------
    // Definición del formulario inicial para subir el archivo
    // -------------------------------------------------------------------------
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('txtFile')
                    ->label('Archivo .txt de Cargos')
                    ->directory('importaciones')
                    ->acceptedFileTypes(['text/plain'])
                    ->required(),
                Forms\Components\Toggle::make('conActualizacion')
                    ->label('¿Con Actualización?'),
                Forms\Components\Toggle::make('nuevosIdentificadores')
                    ->label('¿Nuevos Identificadores?'),
            ]);
    }

    // -------------------------------------------------------------------------
    // Definición de la tabla que muestra los resultados de la primera validación
    // -------------------------------------------------------------------------
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Ejemplo de columnas que muestran los campos parseados
                Tables\Columns\TextColumn::make('codigoNovedad')
                    ->label('Cód. Novedad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numLegajo')
                    ->label('Nro. Legajo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numCargo')
                    ->label('Nro. Cargo'),
                Tables\Columns\TextColumn::make('tipoNovedad')
                    ->label('Tipo Nov.'),
                Tables\Columns\TextColumn::make('errors')
                    ->label('Errores de Validación')
                    ->formatStateUsing(fn ($state) => implode(', ', (array)$state)),
            ])
            ->filters([])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    // -------------------------------------------------------------------------
    // Páginas (acciones) que se crean para el Resource
    // -------------------------------------------------------------------------
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNovedadesCargoImports::route('/'),
            // 'create' => Pages\CreateNovedadesCargoImport::route('/create'),
            // 'edit' => Pages\EditNovedadesCargoImport::route('/{record}/edit'),
            'import' => Pages\NovedadesCargoImport::route('/import'),
            'manage' => Pages\ManageNovedadesCargoImportTemp::route('/manage'),
        ];
    }
}
