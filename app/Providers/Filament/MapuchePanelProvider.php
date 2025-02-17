<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Pages\DatabaseSettings;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Embargos\Resources\EmbargoResource;
use App\Http\Middleware\DatabaseConnectionMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Pages\DashboardSelector;


class MapuchePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('mapuche')
            ->path('mapuche')
            ->profile()
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                ->label('Cambiar Panel')
                ->icon('heroicon-o-arrows-right-left')
                ->url(fn (): string => '/selector-panel'),
            ])
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Mapuche/Resources'), for: 'App\\Filament\\Mapuche\\Resources')
            ->discoverPages(in: app_path('Filament/Mapuche/Pages'), for: 'App\\Filament\\Mapuche\\Pages')
            ->pages([
                Pages\Dashboard::class,
                DashboardSelector::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Mapuche/Widgets'), for: 'App\\Filament\\Mapuche\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
                DatabaseConnectionMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('S.U.C. Mapuche Tools')
            ->navigationGroups([
                'Configuración',
                'Monitoreo'
            ])
            ->pages([
                // Páginas del panel
                DatabaseSettings::class,
            ])
            ->resources([
                // Recursos específicos
                EmbargoResource::class,
            ])
            // ->renderHook(
            //     'panels::body.end',
            //     fn () => view('livewire.panel-switcher-modal')
            // )
            ;

    }
}
