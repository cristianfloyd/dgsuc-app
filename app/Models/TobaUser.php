<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use InvalidArgumentException;
use Override;

use function in_array;

class TobaUser extends Authenticatable
{
    public $incrementing = false;

    public $timestamps = false; // Toba no maneja created_at/updated_at

    protected $connection = 'toba';

    protected $table = 'apex_usuario';

    protected $primaryKey = 'usuario';

    protected $keyType = 'string';

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
        'requiere_segundo_factor',
    ];

    protected $hidden = [
        'clave',
    ];

    // Campos que Laravel espera para autenticación
    #[Override]
    public function getAuthIdentifierName()
    {
        return 'usuario';
    }

    #[Override]
    public function getAuthIdentifier()
    {
        // Generar ID numérico único basado en el username
        // Usando crc32 que genera un entero de 32 bits
        $username = $this->getAttribute('usuario');
        return abs(crc32((string) $username));
    }

    #[Override]
    public function getAuthPassword()
    {
        return $this->getAttribute('clave');
    }

    // Métodos de utilidad basados en los campos de Toba
    public function estaBloqueado(): bool
    {
        return $this->bloqueado == 1;
    }

    public function requiereSegundoFactor(): bool
    {
        return $this->requiere_segundo_factor == 1;
    }

    public function debeForzarCambioPwd(): bool
    {
        return $this->forzar_cambio_pwd == 1;
    }

    public function getParametro($parametro)
    {
        $parametro = strtolower(trim((string) $parametro));
        if (!in_array($parametro, ['a', 'b', 'c'])) {
            throw new InvalidArgumentException("Parámetro '$parametro' es inválido. Debe ser 'a', 'b' o 'c'.");
        }

        $campo = 'parametro_' . $parametro;
        return $this->getAttribute($campo);
    }

    // Scope para usuarios activos (no bloqueados)
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function activos($query)
    {
        return $query->where('bloqueado', 0);
    }

    // Scope para usuarios que no requieren cambio de contraseña
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function sinForzarCambio($query)
    {
        return $query->where('forzar_cambio_pwd', 0);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'bloqueado' => 'boolean',
            'forzar_cambio_pwd' => 'boolean',
            'requiere_segundo_factor' => 'boolean',
            'vencimiento' => 'date',
            'hora_entrada' => 'datetime:H:i:s',
            'hora_salida' => 'datetime:H:i:s',
        ];
    }
}
