<?php

namespace App\Providers\Filament;

use App\Filament\Toba\Auth\Login;
use App\Http\Responses\Auth\TobaLoginResponse;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
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

class TobaPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();
        
        // Registrar respuesta personalizada de login para el panel Toba
        $this->app->bind(LoginResponse::class, TobaLoginResponse::class);
    }
    
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('toba')
            ->path('toba')
            ->login(Login::class)
            ->authGuard('toba')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('DGSUC - UBA')
            ->discoverResources(in: app_path('Filament/Toba/Resources'), for: 'App\\Filament\\Toba\\Resources')
            ->discoverPages(in: app_path('Filament/Toba/Pages'), for: 'App\\Filament\\Toba\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Toba/Widgets'), for: 'App\\Filament\\Toba\\Widgets')
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
