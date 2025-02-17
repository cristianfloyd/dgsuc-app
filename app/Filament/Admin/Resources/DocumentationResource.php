<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Documentation;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Admin\Resources\DocumentationResource\Pages\ViewDocumentation;
use App\Filament\Admin\Resources\DocumentationResource\Pages\ListDocumentation;
use App\Filament\Admin\Resources\DocumentationResource\Pages\CreateDocumentation;
use App\Filament\Admin\Resources\DocumentationResource\Pages\EditDocumentation;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $label = 'Documentacion';
    protected static ?string $pluralLabel = 'Documentacion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Título'),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignorable: fn($record) => $record)
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
            ->actions([
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
                    ->visible(fn(Documentation $record) => file_exists(base_path("docs/filament/{$record->slug}.md")))
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
                ]
            );
        }

        // Optionally remove records that don't have corresponding files
        $existingSlugs = collect($markdownFiles)->map(fn($file) => basename($file, '.md'));
        Documentation::where('section', 'filament')
            ->whereNotIn('slug', $existingSlugs)
            ->delete();
    }
}
