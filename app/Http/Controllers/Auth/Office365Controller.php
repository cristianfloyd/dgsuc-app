<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class Office365Controller extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('azure')->redirect();
    }

    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('azure')->user();

            // Verificar si el usuario ya existe en la base de datos
            $authUser = User::where('email', $user->getEmail())->first();

            if (!$authUser) {
                return redirect()->route('login')->withErrors(['error' => 'El usuario no está registrado en el sistema.']);
            }

            Auth::login($authUser, true);

            return redirect()->route('panel-selector');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Error al iniciar sesión con Office 365']);
        }
    }
}