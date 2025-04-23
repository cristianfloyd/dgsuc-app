<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
            $microsoftUser = Socialite::driver('azure')->user();

            try {
                // Obtener grupos del usuario y verificar permisos
                $groups = $this->getUserGroups($microsoftUser->token);
            } catch (\Exception $e) {
                return redirect()->route('login')
                    ->with('error', $e->getMessage());
            }


            $user = User::where('email', $microsoftUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $microsoftUser->name,
                    'username' => str_replace('@uba.ar', '', $microsoftUser->email),
                    'email' => $microsoftUser->email,
                    'email_verified_at' => now(),
                    'microsoft_id' => $microsoftUser->id,
                    'avatar' => $microsoftUser->avatar,
                    'password' => bcrypt(rand(1000000, 9999999)),
                    'office_groups' => $groups,
                ]);
            } else {
                $user->update([
                    'microsoft_id' => $microsoftUser->id,
                    'avatar' => $microsoftUser->avatar,
                    'office_groups' => $groups,
                ]);
            }

            Auth::login($user);

            return redirect('/selector-panel');
        } catch (\Exception $e) {
            Log::error('Error Microsoft Login: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Error al iniciar sesión con Microsoft: ' . $e->getMessage());
        }
    }

    protected function getUserGroups($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me/memberOf');

            if ($response->successful()) {
                $groups = $response->json()['value'];

                // Verificar si el usuario pertenece a los grupos permitidos
                $allowedGroups = collect($groups)->filter(function ($group) {
                    return $group['displayName'] === 'SUC' ||
                        ($group['mail'] ?? '') === 'SUC1@uba.ar';
                })->toArray();

                if (empty($allowedGroups)) {
                    throw new \Exception('No tienes permiso para acceder. Debes pertenecer al grupo SUC');
                }

                // Filtrar y formatear la información de los grupos
                return collect($allowedGroups)->map(function ($group) {
                    return [
                        'id' => $group['id'],
                        'displayName' => $group['displayName'],
                        'description' => $group['description'] ?? null,
                        'mail' => $group['mail'] ?? null,
                        'groupTypes' => $group['groupTypes'] ?? [],
                    ];
                })->toArray();
            }

            throw new \Exception('No se pudieron obtener los grupos del usuario');
        } catch (\Exception $e) {
            Log::error(__('Error getting Microsoft groups: ') . $e->getMessage());
            throw $e; // Re-lanzar la excepción para que sea capturada en handleMicrosoftCallback
        }
    }
}