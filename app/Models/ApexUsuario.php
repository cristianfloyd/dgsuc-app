<?php

namespace App\Models;

use App\Services\EncodingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'bloqueado' => 'boolean',
        'solicitud_registrar' => 'boolean',
        'dias' => 'integer',
        'forzar_cambio_pwd' => 'boolean',
        'requiere_segundo_factor' => 'boolean',
        'vencimiento' => 'date',
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s',
    ];

    public function nombre(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function parametroA(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function parametroB(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function parametroC(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function solicitudObservacion(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function pUid(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    public function scopeActivos($query)
    {
        return $query->where('bloqueado', 0);
    }

    public function scopePorUsuario($query, $usuario)
    {
        return $query->where('usuario', $usuario);
    }

    public function scopeConEmail($query)
    {
        return $query->whereNotNull('email')->where('email', '!=', '');
    }

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
        if (!\in_array($parametro, ['a', 'b', 'c'])) {
            throw new \InvalidArgumentException("Parámetro '$parametro' es inválido. Debe ser 'a', 'b' o 'c'.");
        }

        $campo = 'parametro_' . $parametro;
        return $this->getAttribute($campo);
    }

    public function tieneVencimiento()
    {
        return $this->vencimiento !== null && $this->vencimiento->isFuture();
    }

    public function estaVencido()
    {
        return $this->vencimiento !== null && $this->vencimiento->isPast();
    }
}
