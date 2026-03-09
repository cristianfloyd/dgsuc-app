<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AfipMapucheSicossNotFoundException extends Exception
{
    /**
     * Constructor de la excepción.
     */
    public function __construct(string $message = 'Registro de AfipMapucheSicoss no encontrado', int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
