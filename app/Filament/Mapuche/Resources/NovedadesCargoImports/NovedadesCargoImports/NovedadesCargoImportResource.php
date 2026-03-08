<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImports\NovedadesCargoImports;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Mapuche\Resources\NovedadesCargoImports\Pages\ListNovedadesCargoImports;
use App\Filament\Mapuche\Resources\NovedadesCargoImports\Pages\NovedadesCargoImport;
use App\Filament\Mapuche\Resources\NovedadesCargoImports\Pages\ManageNovedadesCargoImportTemp;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;
use App\Models\NovedadesCargoImportModel;
use Filament\Forms;
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
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Importar Cargos';

    // -------------------------------------------------------------------------
    // Definición del formulario inicial para subir el archivo
    // -------------------------------------------------------------------------
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('txtFile')
                    ->label('Archivo .txt de Cargos')
                    ->directory('importaciones')
                    ->acceptedFileTypes(['text/plain'])
                    ->required(),
                Toggle::make('conActualizacion')
                    ->label('¿Con Actualización?'),
                Toggle::make('nuevosIdentificadores')
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
                TextColumn::make('codigoNovedad')
                    ->label('Cód. Novedad')
                    ->searchable(),
                TextColumn::make('numLegajo')
                    ->label('Nro. Legajo')
                    ->searchable(),
                TextColumn::make('numCargo')
                    ->label('Nro. Cargo'),
                TextColumn::make('tipoNovedad')
                    ->label('Tipo Nov.'),
                TextColumn::make('errors')
                    ->label('Errores de Validación')
                    ->formatStateUsing(fn ($state) => implode(', ', (array) $state)),
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
            'index' => ListNovedadesCargoImports::route('/'),
            // 'create' => Pages\CreateNovedadesCargoImport::route('/create'),
            // 'edit' => Pages\EditNovedadesCargoImport::route('/{record}/edit'),
            'import' => NovedadesCargoImport::route('/import'),
            'manage' => ManageNovedadesCargoImportTemp::route('/manage'),
        ];
    }
}
