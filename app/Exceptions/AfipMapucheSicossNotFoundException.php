<?php

namespace App\Exceptions;

use Exception;

class AfipMapucheSicossNotFoundException extends Exception
{
    /**
     * Constructor de la excepción.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "Registro de AfipMapucheSicoss no encontrado", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
