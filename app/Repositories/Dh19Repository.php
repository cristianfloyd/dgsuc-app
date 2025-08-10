<?php

namespace App\Repositories;

use App\Contracts\Dh19RepositoryInterface;
use App\Models\Dh19;
use Illuminate\Database\Eloquent\Collection;

class Dh19Repository implements Dh19RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return Dh19::all();
    }

    /**
     * @inheritDoc
     */
    public function findByPrimaryKey(int $nroLegaj, int $codnConce, string $tipoDocum, int $nroDocum): ?Dh19
    {
        return Dh19::where('nro_legaj', $nroLegaj)
            ->where('codn_conce', $codnConce)
            ->where('tipo_docum', $tipoDocum)
            ->where('nro_docum', $nroDocum)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Dh19
    {
        return Dh19::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(Dh19 $dh19, array $data): bool
    {
        return $dh19->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(Dh19 $dh19): bool
    {
        return $dh19->delete();
    }
}
