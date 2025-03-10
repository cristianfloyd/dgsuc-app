<?php

namespace App\Providers;

use App\Livewire\DatabaseConnectionSelector;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar el componente Livewire (Livewire 3 no necesita registro explícito)
        Log::info('FilamentServiceProvider boot render hook');
        // Añadir el componente a la barra de navegación
        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::TOPBAR_END,
        //     fn (): string => '<livewire:database-connection-selector />'
        // );
    }
} 