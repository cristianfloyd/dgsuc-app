<?php

namespace App\Exceptions;

class DuplicateCargoException extends \Exception
{
    protected $message = 'Se encontraron números de cargo duplicados';

    private array $duplicates = [];

    public function __construct(string $message, array $duplicates = [])
    {
        parent::__construct($message);
        $this->duplicates = $duplicates;
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
