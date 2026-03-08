<?php

namespace App\Filament\Admin\Resources\Documentations\Documentations;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use App\Filament\Admin\Resources\Documentations\Pages\CreateDocumentation;
use App\Filament\Admin\Resources\Documentations\Pages\EditDocumentation;
use App\Filament\Admin\Resources\Documentations\Pages\ListDocumentation;
use App\Filament\Admin\Resources\Documentations\Pages\ViewDocumentation;
use App\Models\Documentation;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static string | \UnitEnum | null $navigationGroup = 'Sistema';

    protected static ?string $label = 'Documentacion';

    protected static ?string $pluralLabel = 'Documentacion';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Título'),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->maxLength(255),

                Select::make('section')
                    ->options(Documentation::getSections())
                    ->required()
                    ->label('Sección'),

                MarkdownEditor::make('content')
                    ->required()
                    ->label('Contenido'),

                TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->label('Orden'),

                Toggle::make('is_published')
                    ->label('Publicado')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),

                TextColumn::make('section')
                    ->searchable()
                    ->sortable()
                    ->label('Sección'),

                TextColumn::make('order')
                    ->sortable()
                    ->label('Orden'),

                ToggleColumn::make('is_published')
                    ->label('Publicado'),
            ])
            ->defaultSort('order')
            ->recordActions([
                Action::make('view_docs')
                    ->label('Ver Documentación')
                    ->icon('heroicon-o-document-text')
                    ->action(function (Documentation $record) {
                        $filePath = base_path("docs/filament/{$record->slug}.md");
                        if (file_exists($filePath)) {
                            return response()->file($filePath);
                        }

                        Notification::make()
                            ->title('Documento no encontrado')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Documentation $record) => file_exists(base_path("docs/filament/{$record->slug}.md"))),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentation::route('/'),
            'create' => CreateDocumentation::route('/create'),
            'edit' => EditDocumentation::route('/{record}/edit'),
            'view' => ViewDocumentation::route('/{record}'),
        ];
    }

    public static function syncMarkdownFiles(): void
    {
        $markdownFiles = glob(base_path('docs/filament/*.md'));

        foreach ($markdownFiles as $file) {
            $content = file_get_contents($file);
            $filename = basename($file, '.md');

            // Extract title from markdown first heading
            preg_match('/^#\s*(.+)$/m', $content, $matches);
            $title = $matches[1] ?? $filename;

            Documentation::updateOrCreate(
                ['slug' => $filename],
                [
                    'title' => $title,
                    'content' => $content,
                    'section' => 'filament', // You might want to adjust this
                    'is_published' => true,
                    'order' => 0, // You might want to adjust this
                ],
            );
        }

        // Optionally remove records that don't have corresponding files
        $existingSlugs = collect($markdownFiles)->map(fn ($file) => basename($file, '.md'));
        Documentation::where('section', 'filament')
            ->whereNotIn('slug', $existingSlugs)
            ->delete();
    }
}
