<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImportDataModel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Reportes\Resources\ImportDataResource\Pages;

class ImportDataResource extends Resource
{
    protected static ?string $model = ImportDataModel::class;
    protected static ?string $label = 'Importar Datos';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('excel_file')
                    ->label('Excel File')
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('column1'),
                TextColumn::make('column2'),
                TextColumn::make('column3'),
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
            'edit' => Pages\EditImportData::route('/{record}/edit'),
        ];
    }
}
