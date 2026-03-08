<?php

namespace App\Filament\Admin\Resources\UploadedFiles\UploadedFiles;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\UploadedFiles\Pages\ListUploadedFiles;
use App\Filament\Admin\Resources\UploadedFiles\Pages\CreateUploadedFile;
use App\Filament\Admin\Resources\UploadedFiles\Pages\EditUploadedFile;
use App\Filament\Admin\Resources\UploadedFileResource\Pages;
use App\Models\UploadedFile;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UploadedFileResource extends Resource
{
    protected static ?string $model = UploadedFile::class;

    protected static ?string $label = 'Archivos';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('filename')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('original_name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('file_path')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('user_id')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('user_name')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUploadedFiles::route('/'),
            'create' => CreateUploadedFile::route('/create'),
            'edit' => EditUploadedFile::route('/{record}/edit'),
        ];
    }
}
