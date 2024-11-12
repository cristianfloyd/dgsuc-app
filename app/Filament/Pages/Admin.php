<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class Admin extends Dashboard
{
    protected static ?string $navigatioLabel = 'Panel de Administración';
    protected static ?string $title = 'Panel de Administración';
    protected static ?string $slug = 'admin-settings';
    protected static string $view = 'filament.pages.admin';

}
