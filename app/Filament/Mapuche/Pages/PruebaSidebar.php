<?php

namespace App\Filament\Mapuche\Pages;

use BackedEnum;
use Filament\Pages\Page;

class PruebaSidebar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.mapuche.pages.prueba-sidebar';
}
