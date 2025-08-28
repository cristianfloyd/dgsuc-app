<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ApexUsuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\ApexUsuarioRepositoryInterface;

class EloquentApexUsuarioRepository implements ApexUsuarioRepositoryInterface
{
    public function __construct(
        private readonly ApexUsuario $model,
    ) {
    }

    public function findByUsuario(string $usuario): ?ApexUsuario
    {
        try {
            return $this->model->porUsuario($usuario)->first();
        } catch (\Exception $e) {
            Log::error('Error al buscar usuario por nombre', [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function findByEmail(string $email): ?ApexUsuario
    {
        try {
            return $this->model->where('email', $email)->first();
        } catch (\Exception $e) {
            Log::error('Error al buscar usuario por email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function findByUid(string $uid): ?ApexUsuario
    {
        try {
            return $this->model->where('uid', $uid)->first();
        } catch (\Exception $e) {
            Log::error('Error al buscar usuario por UID', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getUsuariosActivos(): Collection
    {
        try {
            return $this->model->activos()->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios activos', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function getUsuariosBloqueados(): Collection
    {
        try {
            return $this->model->where('bloqueado', 1)->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios bloqueados', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function getUsuariosConVencimiento(): Collection
    {
        try {
            return $this->model->whereNotNull('vencimiento')->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios con vencimiento', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function getUsuariosVencidos(): Collection
    {
        try {
            return $this->model->whereNotNull('vencimiento')
                              ->where('vencimiento', '<', now())
                              ->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios vencidos', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function getUsuariosQueRequierenSegundoFactor(): Collection
    {
        try {
            return $this->model->where('requiere_segundo_factor', 1)->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios que requieren segundo factor', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function getUsuariosConForzarCambio(): Collection
    {
        try {
            return $this->model->where('forzar_cambio_pwd', 1)->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios con forzar cambio', [
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function crear(array $datos): ApexUsuario
    {
        try {
            if (isset($datos['clave'])) {
                $datos['clave'] = Hash::make($datos['clave']);
            }

            return $this->model->create($datos);
        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'datos' => array_diff_key($datos, ['clave' => '']),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function actualizar(string $usuario, array $datos): bool
    {
        try {
            if (isset($datos['clave'])) {
                $datos['clave'] = Hash::make($datos['clave']);
            }

            $usuarioModel = $this->findByUsuario($usuario);
            if (!$usuarioModel) {
                return false;
            }

            return $usuarioModel->update($datos);
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario', [
                'usuario' => $usuario,
                'datos' => array_diff_key($datos, ['clave' => '']),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function bloquear(string $usuario): bool
    {
        return $this->actualizar($usuario, ['bloqueado' => 1]);
    }

    public function desbloquear(string $usuario): bool
    {
        return $this->actualizar($usuario, ['bloqueado' => 0]);
    }

    public function cambiarClave(string $usuario, string $nuevaClave): bool
    {
        return $this->actualizar($usuario, ['clave' => $nuevaClave]);
    }

    public function forzarCambioClave(string $usuario): bool
    {
        return $this->actualizar($usuario, ['forzar_cambio_pwd' => 1]);
    }

    public function eliminarForzarCambioClave(string $usuario): bool
    {
        return $this->actualizar($usuario, ['forzar_cambio_pwd' => 0]);
    }

    public function existeUsuario(string $usuario): bool
    {
        try {
            return $this->model->porUsuario($usuario)->exists();
        } catch (\Exception $e) {
            Log::error('Error al verificar existencia de usuario', [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function buscarPorParametro(string $parametro, string $valor): Collection
    {
        try {
            $parametro = strtolower(trim($parametro));
            if (!in_array($parametro, ['a', 'b', 'c'])) {
                throw new \InvalidArgumentException("Parámetro '$parametro' es inválido. Debe ser 'a', 'b' o 'c'.");
            }

            $campo = 'parametro_' . $parametro;
            return $this->model->where($campo, $valor)->get();
        } catch (\Exception $e) {
            Log::error('Error al buscar por parámetro', [
                'parametro' => $parametro,
                'valor' => $valor,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    public function validarCredenciales(string $usuario, string $clave): bool
    {
        try {
            $usuarioModel = $this->findByUsuario($usuario);
            if (!$usuarioModel) {
                return false;
            }

            if ($usuarioModel->estaBloqueado()) {
                return false;
            }

            return Hash::check($clave, $usuarioModel->clave);
        } catch (\Exception $e) {
            Log::error('Error al validar credenciales', [
                'usuario' => $usuario,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}