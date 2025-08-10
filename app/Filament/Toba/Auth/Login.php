<?php

namespace App\Filament\Toba\Auth;

use App\Http\Responses\Auth\TobaLoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.toba.auth.login';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('usuario')
                    ->label('Usuario')
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),
                    
                TextInput::make('clave')
                    ->label('Contraseña')
                    ->password()
                    ->required()
                    ->revealable(true)
                    ->extraInputAttributes(['tabindex' => 2]),
            ])
            ->statePath('data');
    }
    
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        
        $credentials = [
            'usuario' => $data['usuario'],
            'clave' => $data['clave'],
        ];
        
        // Intentar autenticación con guard Toba
        if (Auth::guard('toba')->attempt($credentials)) {
            $tobaUser = Auth::guard('toba')->user();
            
            // Sincronizar con guard web para compatibilidad
            Auth::guard('web')->login($tobaUser);
            
            session()->regenerate();
            
            // Usar respuesta personalizada que redirige a selector-panel
            return app(TobaLoginResponse::class);
        }
        
        throw ValidationException::withMessages([
            'data.usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }
    
    public function getHeading(): string
    {
        return 'Iniciar Sesión - Sistema Mapuche';
    }
    
    public function getSubheading(): ?string
    {
        return 'Accede con tus credenciales del sistema Mapuche';
    }
}