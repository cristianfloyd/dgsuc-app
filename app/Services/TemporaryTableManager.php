<?php

namespace App\Services;

use App\Repositories\NominaRepository;

class TemporaryTableManager
{
    private $nominaRepository;
    private $tableCreated = false;

    public function __construct(NominaRepository $nominaRepository)
    {
        $this->nominaRepository = $nominaRepository;
    }

    public function createTemporaryTables(int $nroLiqui): void
    {
        if ($this->tableCreated) {
            return;
        }
        
        $this->nominaRepository->createLiquidationTable($nroLiqui);
        $this->nominaRepository->createCheTable();
        $this->nominaRepository->insertInitialCheData();
        $this->nominaRepository->updateDescriptions();

        $this->tableCreated = true;
    }

    public function dropTemporaryTables(): void
    {
        if ($this->tableCreated) {
            $this->nominaRepository->dropTemporaryTables();
            $this->tableCreated = false;
        }
    }

    public function getAportes(): array
    {
        return $this->nominaRepository->getAportes();
    }

    public function getNetosLiquidados(): array
    {
        return $this->nominaRepository->getNetosLiquidados();
    }
}
