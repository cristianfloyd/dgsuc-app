<?php

namespace App\Providers\Filament;

use App\Filament\Afip\Pages\SicossControles;
use App\Filament\Afip\Pages\SicossReportePage;
use App\Filament\Afip\Pages\SicossUpdates;
use App\Filament\Afip\Widgets\AfipRelacionesActivasStats;
use App\Filament\Pages\DashboardSelector;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
                    ->url(fn (): string => '/selector-panel'),
            ])
            ->discoverWidgets(in: app_path('Filament/Afip/Widgets'), for: 'App\\Filament\\Afip\\Widgets')
            ->widgets([
                PeriodoFiscalSelectorWidget::class,
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
