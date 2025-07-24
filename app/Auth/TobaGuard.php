<?php

namespace App\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Log;

class TobaGuard extends SessionGuard
{
    public function attempt(array $credentials = [], $remember = false)
    {
        // Mapear campos de Laravel a Toba
        if (isset($credentials['username'])) {
            $credentials['usuario'] = $credentials['username'];
            unset($credentials['username']);
        }
        
        if (isset($credentials['password'])) {
            $credentials['clave'] = $credentials['password'];
            unset($credentials['password']);
        }

        Log::debug('TobaGuard attempt', ['credentials_keys' => array_keys($credentials)]);
        
        $result = parent::attempt($credentials, $remember);
        
        if ($result && $this->user()) {
            Log::debug('TobaGuard login exitoso', [
                'user_id' => $this->user()->getAuthIdentifier(),
                'username' => $this->user()->getAuthIdentifierName(),
                'user_class' => get_class($this->user())
            ]);
        } else {
            Log::debug('TobaGuard login fallido');
        }
        
        return $result;
    }

    protected function updateSession($id)
    {
        Log::debug('TobaGuard updateSession', [
            'session_user_id' => $id,
            'guard_name' => $this->getName(),
            'user_authenticated' => !is_null($this->user()),
            'session_before' => $this->session->all()
        ]);
        
        $this->session->put($this->getName(), $id);
        $this->session->migrate(true);
        
        Log::debug('TobaGuard updateSession after', [
            'session_after' => $this->session->all(),
            'auth_check' => $this->check()
        ]);
    }
}
