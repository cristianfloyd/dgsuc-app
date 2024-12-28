<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImportDataModel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use App\Filament\Reportes\Resources\ImportDataResource\Pages;

class ImportDataResource extends Resource
{
    protected static ?string $model = ImportDataModel::class;
    protected static ?string $label = 'Importar Datos';
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
                TextColumn::make('fecha_registro'),
                TextColumn::make('nombre'),
                TextColumn::make('email'),
                TextColumn::make('tipo'),
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
            'index' => Pages\ListImportData::route('/'),
            'create' => Pages\CreateImportData::route('/create'),
            'import' => Pages\ImportData::route('/import'),
        ];
    }
}
