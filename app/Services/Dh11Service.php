<?php

namespace App\Services;

use App\Repositories\Dh11RepositoryInterface;

class Dh11Service
{
    private $dh11Repository;

    public function __construct(Dh11RepositoryInterface $dh11Repository)
    {
        $this->dh11Repository = $dh11Repository;
    }

    /**
     * Obtiene una lista de todas las codc_categ.
     *
     * @return array
     */
    public function getAllCodcCateg(): array
    {
        return $this->dh11Repository->getAllCodcCateg();
    }
}
