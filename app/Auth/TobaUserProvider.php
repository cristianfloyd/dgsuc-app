<?php

namespace App\Auth;

use App\Models\User;
use App\Services\TobaApiService;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TobaUserProvider implements UserProvider
{
    protected $tobaApi;
    protected $hasher;
    protected $model;

    public function __construct(TobaApiService $tobaApi ,$hasher, $model)
    {
        $this->tobaApi = $tobaApi;
        $this->hasher = $hasher;
        $this->model = $model;
    }


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
        $response = $this->tobaApi->login($credentials['username'], $credentials['password']);

        if ($response && isset($response['token'])) {
            $userInfo = $this->tobaApi->getUserInfo($response['token']);

            if ($userInfo) {
                return new User([
                    'id' => $userInfo['id'],
                    'name' => $userInfo['name'],
                    'email' => $userInfo['email'],
                    'toba_token' => $response['token'],
                ]);
            }
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Implementa la lógica para validar las credenciales contra Toba
        return true;
    }
    /**
     * @inheritDoc
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false) {
    }
}
