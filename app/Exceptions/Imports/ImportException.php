<?php

namespace App\Exceptions\Imports;

class ImportException extends \Exception
{
    /**
     * Constructor personalizado para mantener el contexto del error original.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
