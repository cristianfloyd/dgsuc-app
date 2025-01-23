<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use App\Models\EmbargoProcesoResult;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\EmbargoRepositoryInterface;

class EmbargoRepository implements EmbargoRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * @var EmbargoProcesoResult
     */
    protected EmbargoProcesoResult $model;

    /**
     * Constructor del repositorio
     *
     * @param EmbargoProcesoResult $model
     */
    public function __construct(EmbargoProcesoResult $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function executeEmbargoProcess(
        array $nroComplementarias,
        int $nroLiquiDefinitiva,
        int $nroLiquiProxima,
        bool $insertIntoDh25
    ): Builder {
        try {
            $arrayString = $this->prepareComplementariasArray($nroComplementarias);

            $results = $this->getConnectionFromTrait()
                ->select("SELECT * FROM suc.emb_proceso( $arrayString, ?, ?, ?)", [
                    $nroLiquiDefinitiva,
                    $nroLiquiProxima,
                    $insertIntoDh25
                ]);

            if (empty($results)) {
                return $this->model->getEmptyQuery();
            }

            // Creamos una nueva instancia del modelo para usar hydrate
            return $this->model->newQuery()->setModel(
                $this->model->newInstance()->hydrate($results)
            );

        } catch (\Exception $e) {
            Log::error('Error en proceso de embargo: ' . $e->getMessage());
            return $this->model->getEmptyQuery();
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllEmbargos(): Collection
    {
        return $this->model->all();
    }

    /**
     * @inheritDoc
     */
    public function getEmbargosByType(int $tipoEmbargo): Collection
    {
        return $this->model->where('tipo_embargo', $tipoEmbargo)->get();
    }

    /**
     * Prepara el array de complementarias para la consulta SQL
     *
     * @param array $nroComplementarias
     * @return string
     */
    private function prepareComplementariasArray(array $nroComplementarias): string
    {
        return empty($nroComplementarias)
            ? 'ARRAY[]::integer[]'
            : 'ARRAY[' . implode(',', array_map('intval', $nroComplementarias)) . ']';
    }
}
