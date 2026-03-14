<?php

namespace App\Exceptions\Imports;

use Exception;
use Throwable;

/** Base exception for import operations. */
class ImportException extends Exception
{
    public function __construct(string $message = 'Import error', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
