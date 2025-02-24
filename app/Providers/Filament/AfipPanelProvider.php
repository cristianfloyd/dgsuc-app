<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Pages\DashboardSelector;
use App\Filament\Afip\Pages\SicossUpdates;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Afip\Pages\SicossControles;
use App\Filament\Afip\Pages\SicossReportePage;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use App\Filament\Afip\Widgets\AfipRelacionesActivasStats;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AfipPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('afip')
            ->path('afip-panel')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Afip/Resources'), for: 'App\\Filament\\Afip\\Resources')
            ->discoverPages(in: app_path('Filament/Afip/Pages'), for: 'App\\Filament\\Afip\\Pages')
            ->pages([
                Pages\Dashboard::class,
                DashboardSelector::class,
                SicossControles::class,
                SicossReportePage::class,
                SicossUpdates::class,
            ])
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                    ->label('Cambiar Panel')
                    ->icon('heroicon-o-arrows-right-left')
                    ->url(fn(): string => '/selector-panel'),
            ])
            ->discoverWidgets(in: app_path('Filament/Afip/Widgets'), for: 'App\\Filament\\Afip\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                AfipRelacionesActivasStats::class,
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
            ->brandName('AFIP Panel')
            ->navigationGroups([
                'AFIP',
                'Configuración',
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->font('Poppins');
    }
}
