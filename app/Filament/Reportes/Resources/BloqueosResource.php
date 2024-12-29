<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImportDataModel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use App\Filament\Reportes\Resources\Bloqueos\Pages;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\ViewBloqueo;
use App\Filament\Reportes\Resources\BloqueosResource\RelationManagers\CargosRelationManager;

class BloqueosResource extends Resource
{
    protected static ?string $model = ImportDataModel::class;
    protected static ?string $label = 'Bloqueos';
    protected static ?string $pluralLabel = 'Bloqueos';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public $excel_file = null;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        FileUpload::make('excel_file')
                            ->label('Archivo Excel')
                            ->preserveFilenames()
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ])
                            ->maxSize(5120)
                            ->downloadable()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_registro')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nombre')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usuario_mapuche')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dependencia'),
                TextColumn::make('nro_legaj'),
                TextColumn::make('nro_cargo'),
                TextColumn::make('fecha_baja')->date(),
                TextColumn::make('cargo.fec_baja')->label('Fecha dh03')->date(),
                TextColumn::make('tipo'),
                TextColumn::make('observaciones')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string{
                        return $column->getState();
                    }),
                IconColumn::make('chkstopliq')->boolean(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CargosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportData::route('/'),
            'create' => Pages\ImportData::route('/crear'),
            'view' => ViewBloqueo::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return 'Nuevo';
    }


}
