<?php

namespace App\DTOs;

/**
 * Representa el resultado de una importación de datos.
 *
 * @property bool $success Indica si la importación fue exitosa.
 * @property string $message Mensaje que describe el resultado de la importación.
 * @property array $processedData Datos procesados durante la importación.
 * @property \Throwable|null $error Excepción que ocurrió durante la importación, si hubo alguna.
 */
class ImportResultDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $processedData = [],
        public ?\Throwable $error = null
    ) {}
}

