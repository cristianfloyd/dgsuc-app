<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ApexUsuario;
use Illuminate\Database\Eloquent\Collection;

interface ApexUsuarioRepositoryInterface
{
    public function findByUsuario(string $usuario): ?ApexUsuario;
    
    public function findByEmail(string $email): ?ApexUsuario;
    
    public function findByUid(string $uid): ?ApexUsuario;
    
    public function getUsuariosActivos(): Collection;
    
    public function getUsuariosBloqueados(): Collection;
    
    public function getUsuariosConVencimiento(): Collection;
    
    public function getUsuariosVencidos(): Collection;
    
    public function getUsuariosQueRequierenSegundoFactor(): Collection;
    
    public function getUsuariosConForzarCambio(): Collection;
    
    public function crear(array $datos): ApexUsuario;
    
    public function actualizar(string $usuario, array $datos): bool;
    
    public function bloquear(string $usuario): bool;
    
    public function desbloquear(string $usuario): bool;
    
    public function cambiarClave(string $usuario, string $nuevaClave): bool;
    
    public function forzarCambioClave(string $usuario): bool;
    
    public function eliminarForzarCambioClave(string $usuario): bool;
    
    public function existeUsuario(string $usuario): bool;
    
    public function buscarPorParametro(string $parametro, string $valor): Collection;
    
    public function validarCredenciales(string $usuario, string $clave): bool;
}