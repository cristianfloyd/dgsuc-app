<?php

namespace App\Auth;

use App\Models\ApexUsuario;
use App\Models\User;
use App\Services\TobaAuthService;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;

class TobaUserProvider implements UserProvider
{
    protected $tobaAuthService;

    public function __construct(TobaAuthService $tobaAuthService)
    {
        $this->tobaAuthService = $tobaAuthService;
    }

    public function retrieveById($identifier)
    {
        // Buscar en la tabla users de Laravel por ID numérico
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // No implementado para este caso
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No implementado para este caso
    }

    public function retrieveByCredentials(array $credentials)
    {
        Log::debug('TobaUserProvider retrieveByCredentials start', ['usuario' => $credentials['usuario'] ?? 'not_set']);
        
        if (!isset($credentials['usuario'])) {
            Log::debug('TobaUserProvider: usuario not set in credentials');
            return null;
        }

        // Buscar usuario de Toba con corrección de encoding automática
        $tobaUser = ApexUsuario::where('usuario', $credentials['usuario'])->first();
        if (!$tobaUser) {
            Log::debug('TobaUserProvider: Toba user not found', ['usuario' => $credentials['usuario']]);
            return null;
        }
        
        Log::debug('TobaUserProvider: Toba user found', ['usuario' => $credentials['usuario']]);

        // Buscar usuario en Laravel: primero por toba_usuario, luego por name
        $laravelUser = User::where('toba_usuario', $credentials['usuario'])->first();
        
        if (!$laravelUser) {
            // Si no existe por toba_usuario, buscar por name o username (usuario existente de Laravel)
            $laravelUser = User::where('name', $credentials['usuario'])
                              ->orWhere('username', $credentials['usuario'])
                              ->first();
            
            if ($laravelUser) {
                // Usuario existente en Laravel: agregar toba_usuario
                $laravelUser->update([
                    'toba_usuario' => $credentials['usuario'],
                    'name' => $credentials['usuario'], // Asegurar que sean iguales
                    'username' => $credentials['usuario'], // username = toba_usuario
                    'email' => $tobaUser->email ?: $laravelUser->email, // Mantener email si Toba no lo tiene
                ]);
                
                Log::debug('Updated existing Laravel user with Toba data', [
                    'toba_usuario' => $credentials['usuario'],
                    'laravel_user_id' => $laravelUser->id
                ]);
            } else {
                // Crear nuevo usuario en Laravel basado en datos de Toba
                $laravelUser = User::create([
                    'name' => $credentials['usuario'], // name = toba_usuario
                    'username' => $credentials['usuario'], // username = toba_usuario
                    'email' => $tobaUser->email ?? $credentials['usuario'] . '@toba.local',
                    'password' => bcrypt('toba_user'), // Password dummy, no se usa
                    'toba_usuario' => $credentials['usuario'],
                    'email_verified_at' => now(),
                ]);
                
                Log::debug('Created new Laravel user for Toba user', [
                    'toba_usuario' => $credentials['usuario'],
                    'laravel_user_id' => $laravelUser->id
                ]);
            }
        } else {
            // Usuario ya sincronizado: actualizar datos si es necesario
            $updates = [];
            
            if ($laravelUser->name !== $credentials['usuario']) {
                $updates['name'] = $credentials['usuario'];
            }
            
            if ($laravelUser->username !== $credentials['usuario']) {
                $updates['username'] = $credentials['usuario'];
            }
            
            if ($tobaUser->email && $laravelUser->email !== $tobaUser->email) {
                $updates['email'] = $tobaUser->email;
            }
            
            if (!empty($updates)) {
                $laravelUser->update($updates);
                Log::debug('Updated synchronized Laravel user', [
                    'toba_usuario' => $credentials['usuario'],
                    'updates' => $updates
                ]);
            }
        }

        // Agregar datos de Toba al usuario de Laravel para acceso posterior
        $laravelUser->toba_data = $tobaUser;
        
        Log::debug('TobaUserProvider retrieveByCredentials success', [
            'laravel_user_id' => $laravelUser->id,
            'usuario' => $credentials['usuario']
        ]);
        
        return $laravelUser;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        Log::debug('TobaUserProvider validateCredentials start', [
            'user_id' => $user->id,
            'usuario' => $credentials['usuario'] ?? 'not_set'
        ]);
        
        $result = $this->tobaAuthService->autenticar(
            $credentials['usuario'],
            $credentials['clave']
        );
        
        Log::debug('TobaUserProvider validateCredentials result', [
            'result' => $result,
            'usuario' => $credentials['usuario']
        ]);
        
        return $result;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No necesario para Toba
    }
}