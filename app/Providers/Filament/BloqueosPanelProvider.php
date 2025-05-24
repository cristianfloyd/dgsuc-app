<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Pages\DashboardSelector;
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

class BloqueosPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('bloqueos')
            ->path('bloqueos')
            ->colors([
                'primary' => Color::Orange,
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Bloqueos/Resources'), for: 'App\\Filament\\Bloqueos\\Resources')
            ->discoverPages(in: app_path('Filament/Bloqueos/Pages'), for: 'App\\Filament\\Bloqueos\\Pages')
            ->pages([
                Pages\Dashboard::class,
                DashboardSelector::class,
            ])
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                ->label('Cambiar Panel')
                ->icon('heroicon-o-arrows-right-left')
                ->url(fn (): string => '/selector-panel'),
            ])
            ->discoverWidgets(in: app_path('Filament/Bloqueos/Widgets'), for: 'App\\Filament\\Bloqueos\\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('Panel de Bloqueos')
            ->navigationGroups([
                'Bloqueos',
                'Configuración',
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full');
    }
}
