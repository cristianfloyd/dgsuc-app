<?php

namespace App\Filament\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TobaLogin extends Login
{
    protected string $view = 'filament.auth.toba-login';

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->extraInputAttributes(['tabindex' => 2]),
            ])
            ->statePath('data');
    }

    #[\Override]
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

            // Sincronizar con guard web
            Auth::guard('web')->login($tobaUser);

            session()->regenerate();

            return resolve(LoginResponse::class);
        }

        throw ValidationException::withMessages([ 'data.usuario' => 'Las credenciales no coinciden con nuestros registros.', ]);
    }

    #[\Override]
    public function getHeading(): string
    {
        return 'Iniciar Sesión - Sistema Toba';
    }

    #[\Override]
    public function getSubheading(): ?string
    {
        return 'Accede con tus credenciales del sistema Toba';
    }

    #[\Override]
    protected function getViewData(): array
    {
        return [
            'brandName' => config('app.name'),
        ];
    }
}
