<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DashboardSelector;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LiquidacionesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('liquidaciones')
            ->path('liquidaciones')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Personal'),
                NavigationGroup::make()
                    ->label('Liquidaciones'),
            ])
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                    ->label('Cambiar Panel')
                    ->icon('heroicon-o-arrows-right-left')
                    ->url(fn (): string => '/selector-panel'),
            ])
            ->discoverResources(in: app_path('Filament/Liquidaciones/Resources'), for: 'App\\Filament\\Liquidaciones\\Resources')
            ->discoverPages(in: app_path('Filament/Liquidaciones/Pages'), for: 'App\\Filament\\Liquidaciones\\Pages')
            ->pages([
                Pages\Dashboard::class,
                DashboardSelector::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Liquidaciones/Widgets'), for: 'App\\Filament\\Liquidaciones\\Widgets')
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
            ->breadcrumbs(true)
            ->maxContentWidth('full')
            ->sidebarFullyCollapsibleOnDesktop();
    }
}
