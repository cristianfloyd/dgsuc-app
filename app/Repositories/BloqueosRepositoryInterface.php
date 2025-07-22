<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface BloqueosRepositoryInterface
{
    public function getTotalProcesados(): int;

    public function getTotalPendientes(): int;

    public function getPorEstado(string $estado): Collection;

    public function validarRegistro($registro): array;

    public function procesarBloqueos(): Collection;
    // Otros métodos según necesidades
}
