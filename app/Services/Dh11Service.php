<?php

namespace App\Services;

use App\Repositories\Dh11RepositoryInterface;

class Dh11Service
{
    public function __construct(private readonly Dh11RepositoryInterface $dh11Repository)
    {
    }

    /**
     * Obtiene una lista de todas las codc_categ.
     */
    public function getAllCodcCateg(): array
    {
        return $this->dh11Repository->getAllCodcCateg();
    }
}
