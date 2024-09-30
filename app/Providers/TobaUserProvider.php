<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // Implementa la lógica para recuperar un usuario por ID desde Toba
    }

    public function retrieveByToken($identifier, $token)
    {
        // No es necesario implementar si no usas "remember me"
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No es necesario implementar si no usas "remember me"
    }

    public function retrieveByCredentials(array $credentials)
    {
        // Implementa la lógica para recuperar un usuario por credenciales desde Toba
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Implementa la lógica para validar las credenciales contra Toba
    }
    /**
     * @inheritDoc
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false) {
    }
}
