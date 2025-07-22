<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UploadedFileResource\Pages;
use App\Models\UploadedFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UploadedFileResource extends Resource
{
    protected static ?string $model = UploadedFile::class;

    protected static ?string $label = 'Archivos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('filename')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('original_name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('file_path')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('user_id')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('user_name')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUploadedFiles::route('/'),
            'create' => Pages\CreateUploadedFile::route('/create'),
            'edit' => Pages\EditUploadedFile::route('/{record}/edit'),
        ];
    }
}
