<?php

namespace App\Models;

use App\Services\EncodingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Override;

use function in_array;

class ApexUsuario extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $connection = 'toba';

    protected $table = 'apex_usuario';

    protected $primaryKey = 'usuario';

    protected $keyType = 'string';

    protected $fillable = [
        'usuario',
        'clave',
        'nombre',
        'email',
        'autentificacion',
        'bloqueado',
        'parametro_a',
        'parametro_b',
        'parametro_c',
        'solicitud_registrar',
        'solicitud_obs_tipo_proyecto',
        'solicitud_obs_tipo',
        'solicitud_observacion',
        'usuario_tipodoc',
        'pre',
        'ciu',
        'suf',
        'telefono',
        'vencimiento',
        'dias',
        'hora_entrada',
        'hora_salida',
        'ip_permitida',
        'forzar_cambio_pwd',
        'requiere_segundo_factor',
        'uid',
        'p_uid',
    ];

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

    public function tieneVencimiento(): bool
    {
        return $this->vencimiento !== null && $this->vencimiento->isFuture();
    }

    public function estaVencido(): bool
    {
        return $this->vencimiento !== null && $this->vencimiento->isPast();
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function parametroA(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function parametroB(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function parametroC(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function solicitudObservacion(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function pUid(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value): ?string => EncodingService::toUtf8($value),
            set: fn(?string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function activos($query)
    {
        return $query->where('bloqueado', 0);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porUsuario($query, $usuario)
    {
        return $query->where('usuario', $usuario);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function conEmail($query)
    {
        return $query->whereNotNull('email')->where('email', '!=', '');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'bloqueado' => 'boolean',
            'solicitud_registrar' => 'boolean',
            'dias' => 'integer',
            'forzar_cambio_pwd' => 'boolean',
            'requiere_segundo_factor' => 'boolean',
            'vencimiento' => 'date',
            'hora_entrada' => 'datetime:H:i:s',
            'hora_salida' => 'datetime:H:i:s',
        ];
    }
}
