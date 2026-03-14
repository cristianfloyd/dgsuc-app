<?php

namespace App\Exceptions;

use Exception;

class DuplicateCargoException extends Exception
{
    protected $message = 'Se encontraron números de cargo duplicados';

    public function __construct(string $message, private readonly array $duplicates = [])
    {
        parent::__construct($message);
    }

    /**
     * Obtiene los números de cargo duplicados.
     *
     * @return array Array asociativo [nro_cargo => cantidad_repeticiones]
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
}
