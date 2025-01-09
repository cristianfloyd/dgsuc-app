<?php

namespace App\Exceptions;

use Exception;

class DuplicateCargoException extends Exception
{
    protected $message = 'Se encontraron números de cargo duplicados';
}
