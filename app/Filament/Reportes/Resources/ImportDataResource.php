<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\ImportData;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Reportes\Resources\ImportDataResource\Pages;
use App\Filament\Reportes\Resources\ImportDataResource\RelationManagers;

class ImportDataResource extends Resource
{
    protected static ?string $model = ImportData::class;
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
                Tables\Columns\TextColumn::make('column1'),
                Tables\Columns\TextColumn::make('column2'),
                Tables\Columns\TextColumn::make('column3'),
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
