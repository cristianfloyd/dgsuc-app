<?php

namespace App\Exceptions\Imports;

use Exception;
use Throwable;

/** Exception thrown when a duplicate import is attempted. */
class DuplicateImportException extends Exception
{
    public function __construct(string $message = 'Duplicate import', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
