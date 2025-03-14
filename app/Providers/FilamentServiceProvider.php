<?php

namespace App\Providers;

use Livewire\Livewire;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use App\Services\DatabaseConnectionService;
use App\Livewire\DatabaseConnectionSelector;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseConnectionService::class, function () {
            return new DatabaseConnectionService();
        });
    }

    public function boot(): void
    {
        // Registrar el componente Livewire (Livewire 3 no necesita registro explícito)
        // Log::info('FilamentServiceProvider boot render hook');
        // Añadir el componente a la barra de navegación
        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::TOPBAR_END,
        //     fn (): string => '<livewire:database-connection-selector />'
        // );
        // Registrar el renderHook para el footer
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => view('components.filament.footer-branding')->render()
        );
    }
}
