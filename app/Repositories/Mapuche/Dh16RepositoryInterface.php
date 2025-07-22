<?php

declare(strict_types=1);

namespace App\Repositories\Mapuche;

use App\Data\Mapuche\Dh16Data;
use Illuminate\Support\Collection;

interface Dh16RepositoryInterface
{
    /**
     * Get all conceptos by grupo.
     *
     * @param int $codn_grupo
     *
     * @return Collection<int, Dh16Data>
     */
    public function getConceptosByGrupo(int $codn_grupo): Collection;

    /**
     * Create new concepto grupo relation.
     */
    public function create(Dh16Data $data): Dh16Data;

    /**
     * Delete concepto grupo relation.
     */
    public function delete(int $codn_grupo, int $codn_conce): bool;
}
