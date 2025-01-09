<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Livewire\Livewire;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Livewire\Filament\Reportes\Components\BloqueosProcessor;
use App\Filament\Reportes\Resources\EmbargoResource\Pages\ReporteEmbargos;

class ReportesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('reportes')
            ->path('reportes')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/reportes/theme.css')
            ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Informes')
                    ->icon('heroicon-o-document-chart-bar'),
                NavigationGroup::make()
                    ->label('Configuración')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                ->label('Cambiar Panel')
                ->icon('heroicon-o-arrows-right-left')
                ->url(fn (): string => '/selector-panel'),
            ])
            ->brandName('Panel de Reportes')
            ->discoverResources(in: app_path('Filament/Reportes/Resources'), for: 'App\\Filament\\Reportes\\Resources')
            ->discoverPages(in: app_path('Filament/Reportes/Pages'), for: 'App\\Filament\\Reportes\\Pages')
            ->pages([
                Pages\Dashboard::class,
                // ReporteEmbargos::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Reportes/Widgets'), for: 'App\\Filament\\Reportes\\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->breadcrumbs(true)
            ->maxContentWidth('full')
            ->sidebarFullyCollapsibleOnDesktop();
    }

    public function boot()
    {
        Livewire::component('bloqueos-processor', BloqueosProcessor::class);
    }
}
