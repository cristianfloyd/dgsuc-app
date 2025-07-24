<?php

namespace App\Http\Controllers\Auth;

use App\Models\TobaUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TobaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.toba-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
        ]);

        // Verificar si el usuario existe y no está bloqueado antes de intentar login
        $tobaUser = TobaUser::where('usuario', $credentials['usuario'])->first();
        
        if (!$tobaUser) {
            return back()->withErrors([
                'usuario' => 'El usuario no existe.',
            ]);
        }

        if ($tobaUser->estaBloqueado()) {
            return back()->withErrors([
                'usuario' => 'El usuario está bloqueado.',
            ]);
        }

        if (Auth::guard('toba')->attempt($credentials)) {
            $request->session()->regenerate();
            
            // Sincronizar con guard por defecto para que sessions tenga user_id correcto
            $tobaAuthUser = Auth::guard('toba')->user();
            Auth::guard('web')->login($tobaAuthUser);
            
            // Debug: verificar estado del usuario autenticado
            Log::debug('Post-login state', [
                'toba_user' => Auth::guard('toba')->user()?->getAuthIdentifier(),
                'default_user' => Auth::user()?->getAuthIdentifier(),
                'session_id_before_check' => session()->getId()
            ]);
            
            // Obtener datos de Toba del usuario autenticado
            $user = Auth::guard('toba')->user();
            $tobaData = $user->toba_data ?? $tobaUser;
            
            // Verificar si requiere cambio de contraseña
            if ($tobaData->debeForzarCambioPwd()) {
                return redirect()->route('password.change')
                    ->with('warning', 'Debe cambiar su contraseña antes de continuar.');
            }
            
            // Verificar si requiere segundo factor
            if ($tobaData->requiereSegundoFactor()) {
                return redirect()->route('two-factor.verify')
                    ->with('info', 'Ingrese su segundo factor de autenticación.');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('toba')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
