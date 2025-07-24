<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TobaUser extends Authenticatable
{
    protected $connection = 'toba';
    protected $table = 'apex_usuario';
    protected $primaryKey = 'usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Toba no maneja created_at/updated_at
    
    protected $fillable = [
        'usuario', 
        'nombre', 
        'email',
        'autentificacion',
        'bloqueado',
        'parametro_a',
        'parametro_b', 
        'parametro_c',
        'forzar_cambio_pwd',
        'requiere_segundo_factor'
    ];

    protected $hidden = [
        'clave'
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
        'forzar_cambio_pwd' => 'boolean',
        'requiere_segundo_factor' => 'boolean',
        'vencimiento' => 'date',
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s'
    ];

    // Campos que Laravel espera para autenticación
    public function getAuthIdentifierName()
    {
        return 'usuario';
    }

    public function getAuthIdentifier()
    {
        // Generar ID numérico único basado en el username
        // Usando crc32 que genera un entero de 32 bits
        $username = $this->getAttribute('usuario');
        return abs(crc32($username));
    }

    public function getAuthPassword()
    {
        return $this->getAttribute('clave');
    }

    // Métodos de utilidad basados en los campos de Toba
    public function estaBloqueado()
    {
        return $this->bloqueado == 1;
    }

    public function requiereSegundoFactor()
    {
        return $this->requiere_segundo_factor == 1;
    }

    public function debeForzarCambioPwd()
    {
        return $this->forzar_cambio_pwd == 1;
    }

    public function getParametro($parametro)
    {
        $parametro = strtolower(trim($parametro));
        if (!in_array($parametro, ['a', 'b', 'c'])) {
            throw new \InvalidArgumentException("Parámetro '$parametro' es inválido. Debe ser 'a', 'b' o 'c'.");
        }
        
        $campo = 'parametro_' . $parametro;
        return $this->getAttribute($campo);
    }

    // Scope para usuarios activos (no bloqueados)
    public function scopeActivos($query)
    {
        return $query->where('bloqueado', 0);
    }

    // Scope para usuarios que no requieren cambio de contraseña
    public function scopeSinForzarCambio($query)
    {
        return $query->where('forzar_cambio_pwd', 0);
    }
}