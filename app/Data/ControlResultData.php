<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ControlResultData extends Data
{
    /**
     * Constructor para los resultados de un control post-liquidación.
     *
     * @param bool $success Indica si el control fue exitoso (true) o detectó errores (false)
     * @param string $message Mensaje descriptivo del resultado del control
     * @param array|Collection $data Datos resultantes del control para análisis posterior
     * @param int $count Cantidad de registros encontrados en el control
     * @param string $tipo Tipo de control ejecutado (categorización)
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array|Collection $data = [],
        public ?int $count = null,
        public ?string $tipo = null,
    ) {
        // Si no se proporciona un count explícito, intentamos calcularlo desde los datos
        if ($this->count === null) {
            $this->count = is_countable($this->data) ? \count($this->data) : 0;
        }
    }

    /**
     * Método helper para crear un resultado exitoso.
     */
    public static function success(string $message, array|Collection $data = [], ?string $tipo = null): self
    {
        return new self(
            success: true,
            message: $message,
            data: $data,
            tipo: $tipo,
        );
    }

    /**
     * Método helper para crear un resultado de error.
     */
    public static function error(string $message, array|Collection $data = [], ?string $tipo = null): self
    {
        return new self(
            success: false,
            message: $message,
            data: $data,
            tipo: $tipo,
        );
    }

    /**
     * Verifica si hay errores (registros) en los datos.
     */
    public function tieneRegistros(): bool
    {
        return $this->count > 0;
    }

    /**
     * Devuelve los datos como una colección de Laravel.
     */
    public function toCollection(): Collection
    {
        if ($this->data instanceof Collection) {
            return $this->data;
        }

        return collect($this->data);
    }

    /**
     * Obtiene el estado como texto para mostrar en la interfaz.
     */
    public function getEstadoTexto(): string
    {
        if ($this->success) {
            return 'completado';
        }

        return $this->tieneRegistros() ? 'error' : 'pendiente';
    }

    /**
     * Obtiene el color para el badge de estado en la interfaz.
     */
    public function getEstadoColor(): string
    {
        return match ($this->getEstadoTexto()) {
            'completado' => 'success',
            'error' => 'danger',
            'pendiente' => 'warning',
            default => 'gray'
        };
    }
}
