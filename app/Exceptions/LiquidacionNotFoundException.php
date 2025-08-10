<?php

namespace App\Exceptions;

class LiquidacionNotFoundException extends \Exception
{
    public function __construct(string $message = 'Liquidación no encontrada')
    {
        parent::__construct($message);
    }
}
