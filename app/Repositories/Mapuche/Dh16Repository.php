<?php

declare(strict_types=1);

namespace App\Repositories\Mapuche;

use App\Data\Mapuche\Dh16Data;
use App\Models\Mapuche\Dh16;
use Illuminate\Support\Collection;

class Dh16Repository implements Dh16RepositoryInterface
{
    public function __construct(
        private readonly Dh16 $model,
    ) {
    }

    public function getConceptosByGrupo(int $codn_grupo): Collection
    {
        return $this->model
            ->where('codn_grupo', $codn_grupo)
            ->get()
            ->map(fn (Dh16 $dh16) => Dh16Data::from($dh16));
    }

    public function create(Dh16Data $data): Dh16Data
    {
        $dh16 = $this->model->create([
            'codn_grupo' => $data->codn_grupo,
            'codn_conce' => $data->codn_conce,
        ]);

        return Dh16Data::from($dh16);
    }

    public function delete(int $codn_grupo, int $codn_conce): bool
    {
        return (bool)$this->model
            ->where('codn_grupo', $codn_grupo)
            ->where('codn_conce', $codn_conce)
            ->delete();
    }
}
