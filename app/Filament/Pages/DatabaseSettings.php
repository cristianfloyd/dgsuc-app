<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DatabaseSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Configuración BD';
    protected static ?string $title = 'Configuración de Base de Datos';
    protected static ?string $slug = 'database-settings';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.database-settings';
}
