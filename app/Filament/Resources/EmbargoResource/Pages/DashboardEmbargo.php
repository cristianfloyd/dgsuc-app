<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\EmbargoResource;

class DashboardEmbargo extends Page
{
    protected static string $resource = EmbargoResource::class;

    protected static string $view = 'filament.resources.embargo-resource.pages.dashboard';

    public function mount()
    {
        // Aquí inicializar cualquier lógica necesaria para la página
    }


    public function render(): \Illuminate\Contracts\View\View
    {
        return view(static::$view);

    }
}
