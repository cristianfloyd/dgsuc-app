<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function strlen;

class TobaAuthService
{
    /**
     * Replica la lógica de autenticación de Toba.
     */
    public function autenticar($id_usuario, string $clave): bool
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
        } catch (Exception $e) {
            Log::error('Error en autenticación Toba: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Obtiene información de autenticación del usuario desde apex_usuario.
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
                'requiere_segundo_factor',
            ])
            ->where('usuario', $id_usuario)
            ->first();
    }

    /**
     * Procesa la clave según el algoritmo de Toba.
     */
    private function procesarClave(string $clave, $algoritmo, ?string $clave_almacenada): string
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
     * Implementación exacta de la función encriptar_con_sal de Toba.
     */
    private function encriptarConSal(string $clave, mixed $metodo, ?string $sal = null): string
    {
        $hasher = new TobaHashAdapter($metodo);

        if ($sal === null) {
            return $hasher->hash($clave);
        }

        $resultado = $hasher->getHashVerificador($clave, $sal);

        if (strlen($resultado) > 13) {
            return $resultado;
        }

        if ($metodo === 'bcrypt') {
            return hash('sha256', $this->getSalt() . $resultado);
        }

        $salPrefijo = substr($sal, 0, 10);

        return $salPrefijo . hash((string) $metodo, $salPrefijo . $clave);
    }

    /**
     * Genera un salt aleatorio (método simple para fallback).
     */
    private function getSalt(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    }
}
