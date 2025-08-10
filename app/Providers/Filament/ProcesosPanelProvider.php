<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DashboardSelector;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ProcesosPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('procesos')
            ->path('procesos')
            ->colors([
                'primary' => Color::Fuchsia,
            ])
            ->discoverResources(in: app_path('Filament/ProcesosPanel/Resources'), for: 'App\\Filament\\ProcesosPanel\\Resources')
            ->discoverPages(in: app_path('Filament/ProcesosPanel/Pages'), for: 'App\\Filament\\ProcesosPanel\\Pages')
            ->pages([
                Pages\Dashboard::class,
                DashboardSelector::class,
            ])
            ->discoverWidgets(in: app_path('Filament/ProcesosPanel/Widgets'), for: 'App\\Filament\\ProcesosPanel\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ]);
    }
}
