<?php

namespace App\Services;

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba
     */
    public function autenticar($id_usuario, $clave)
    {
        try {
            // Obtener datos del usuario desde la BD de Toba
            $datos_usuario = $this->getInfoAutenticacion($id_usuario);
            
            if (empty($datos_usuario)) {
                Log::error("El usuario '$id_usuario' no existe en Toba");
                return false;
            }

            // Verificar si el usuario está bloqueado
            if ($datos_usuario->bloqueado == 1) {
                Log::error("El usuario '$id_usuario' está bloqueado");
                return false;
            }

            // Procesar la clave según el algoritmo
            $algoritmo = $datos_usuario->autentificacion ?? 'plano';
            $clave_procesada = $this->procesarClave($clave, $algoritmo, $datos_usuario->clave);

            // Verificar con hash_equals (timing attack safe)
            if (!hash_equals($datos_usuario->clave, $clave_procesada)) {
                Log::error("El usuario '$id_usuario' ingresó una clave incorrecta");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en autenticación Toba: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario desde apex_usuario
     */
    private function getInfoAutenticacion($id_usuario)
    {
        return DB::connection('toba')
            ->table('apex_usuario')
            ->select([
                'usuario', 
                'clave', 
                'autentificacion', 
                'nombre', 
                'email',
                'bloqueado',
                'forzar_cambio_pwd',
                'requiere_segundo_factor'
            ])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba
     */
    private function procesarClave($clave, $algoritmo, $clave_almacenada)
    {
        if ($algoritmo === 'plano') {
            return $clave;
        }
        
        if ($algoritmo === 'md5') {
            return hash('md5', $clave);
        }
        
        // Para otros algoritmos, necesitas implementar encriptar_con_sal
        return $this->encriptarConSal($clave, $algoritmo, $clave_almacenada);
    }

    /**
     * Implementación exacta de la función encriptar_con_sal de Toba
     */
    private function encriptarConSal($clave, $metodo, $sal = null)
    {
        if (version_compare(PHP_VERSION, '5.3.2') >= 0 || $metodo == 'bcrypt') {
            $hasher = new TobaHashAdapter($metodo);
            if (is_null($sal)) {
                // Hash nuevo
                return $hasher->hash($clave);
            } else {
                // Verificación - $sal es la clave almacenada
                $resultado = $hasher->getHashVerificador($clave, $sal);
                if (strlen($resultado) > 13) {
                    return $resultado;
                }
            }
        }

        // Fallback para métodos antiguos
        if (is_null($sal)) {
            $sal = $this->getSalt();
        } else {
            $sal = substr($sal, 0, 10);
        }

        // Si el mecanismo es bcrypt no debería llegar hasta aquí
        return ($metodo != 'bcrypt') ? 
            $sal . hash($metodo, $sal . $clave) : 
            hash('sha256', $this->getSalt() . ($resultado ?? ''));
    }

    /**
     * Genera un salt aleatorio (método simple para fallback)
     */
    private function getSalt()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}