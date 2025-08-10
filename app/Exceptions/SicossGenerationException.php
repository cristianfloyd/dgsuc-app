<?php

namespace App\Exceptions;

/**
 * Excepción específica para errores en la generación de archivos SICOSS
 * Permite un manejo más granular de errores relacionados con AFIP.
 */
class SicossGenerationException extends \Exception
{
    /**
     * Constructor de la excepción.
     *
     * @param string $message Mensaje de error
     * @param int $code Código de error
     * @param \Exception|null $previous Excepción anterior
     */
    public function __construct(string $message = '', int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Representación en string de la excepción.
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
