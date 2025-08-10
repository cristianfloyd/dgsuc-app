<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Dh03Resource\Widgets\CargosOverTime;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SucPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->login()
            ->profile()
            ->userMenuItems([
                'panel-selector' => MenuItem::make()
                    ->label('Cambiar Panel')
                    ->icon('heroicon-o-arrows-right-left')
                    ->url(fn (): string => '/selector-panel'),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([

            ])
            ->profile()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                CargosOverTime::class,
                ActualizarImppBasicWidget::class,
                PeriodoFiscalSelectorWidget::class,
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Afip'),
                NavigationGroup::make()
                    ->label('Reportes')
                    ->icon('heroicon-o-pencil'),
                NavigationGroup::make()
                    ->label(fn (): string => __('navigation.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
                NavigationGroup::make('Usuarios'),
            ])
            ->topNavigation()
            ->breadcrumbs(true)
            ->maxContentWidth('full')
            ->sidebarFullyCollapsibleOnDesktop();
    }
}
