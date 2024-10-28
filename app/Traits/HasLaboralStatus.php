<?php

namespace App\Traits;

trait HasLaboralStatus
{
    /**
     * Estados laborales disponibles
     */
    public const string STATUS_PERMANENT = 'P';    // Permanente
    public const string STATUS_CONTRACT = 'C';      // Contratado
    public const string STATUS_AD_HONOREM = 'A';    // Ad Honorem
    public const string STATUS_SCHOLARSHIP = 'B';    // Beca
    public const string STATUS_SUBSTITUTE = 'S';     // Suplente
    public const string STATUS_OTHER = 'O';         // Otro

    /**
     * Obtiene el estado laboral en formato legible
     */
    public function getLaboralStatusAttribute(): string
    {
        return match($this->estadolaboral) {
            self::STATUS_PERMANENT => 'Permanente',
            self::STATUS_CONTRACT => 'Contratado',
            self::STATUS_AD_HONOREM => 'Ad Honorem',
            self::STATUS_SCHOLARSHIP => 'Beca',
            self::STATUS_SUBSTITUTE => 'Suplente',
            self::STATUS_OTHER => 'Otro',
            default => 'Desconocido'
        };
    }

    /**
     * Scope para filtrar por estado laboral
     */
    public function scopeWithLaboralStatus($query, string $status)
    {
        return $query->where('estadolaboral', $status);
    }
}
