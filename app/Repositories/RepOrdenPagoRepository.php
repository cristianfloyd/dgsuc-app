<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Reportes\RepOrdenPagoModel;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\RepOrdenPagoRepositoryInterface;

class RepOrdenPagoRepository implements RepOrdenPagoRepositoryInterface
{

    public function __construct(protected RepOrdenPagoModel $model)
    {}
    /**
     * Obtiene todas las instancias de RepOrdenPagoModel.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array|int|null $nroLiquis = null): Collection
    {
        $query = RepOrdenPagoModel::query()
            ->orderBy('banco', 'desc')
            ->orderBy('codn_funci', 'asc')
            ->orderBy('codn_fuent', 'asc')
            ->orderBy('codc_uacad', 'asc');

        if (is_array($nroLiquis)) {
            $query->whereIn(column: 'nro_liqui', values: $nroLiquis);
        } elseif (is_int(value: $nroLiquis)) {
            $query->where(column: 'nro_liqui', operator: $nroLiquis);
        }

        return $query->get();
    }

    /**
     * Obtiene todas las instancias de RepOrdenPagoModel junto con su unidad académica.
     *
     * @param array|int|null $nroLiquis Un arreglo de números de liquidación o un número de liquidación individual para filtrar los resultados. Si se omite, se devolverán todos los registros.
     * @return Collection La colección de instancias de RepOrdenPagoModel con su unidad académica.
     */
    public function getAllWithUnidadAcademica(array|int|null $nroLiquis = null): Collection
    {
        $query = RepOrdenPagoModel::with(relations: ['unidadAcademica' => function ($query): void {
            $query->select('nro_tabla', 'desc_abrev', 'desc_item');
        }])
        ->orderBy('banco', 'desc')
        ->orderBy('codn_funci', 'asc')
        ->orderBy('codn_fuent', 'asc')
        ->orderBy('codc_uacad', 'asc');

        if (is_array($nroLiquis)) {
            $query->whereIn(column: 'nro_liqui', values: $nroLiquis);
        } elseif (is_int(value: $nroLiquis)) {
            $query->where(column: 'nro_liqui', operator: $nroLiquis);
        }

        return $query->get();
    }

    /**
     * Obtiene la primera instancia de RepOrdenPagoModel que coincida con el número de liquidación proporcionado.
     *
     * @param int $nroLiqui El número de liquidación a buscar.
     * @return RepOrdenPagoModel|null La primera instancia de RepOrdenPagoModel que coincida con el número de liquidación, o null si no se encuentra ninguna.
     */
    public function getByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return RepOrdenPagoModel::where('nro_liqui', $nroLiqui)->first();
    }


    /**
     * Crea una nueva instancia de RepOrdenPagoModel con los datos proporcionados.
     *
     * @param array $data Los datos para crear la nueva instancia.
     * @return RepOrdenPagoModel La nueva instancia creada.
     */
    public function create(array $data): RepOrdenPagoModel
    {
        return RepOrdenPagoModel::create($data);
    }


    /**
     * Actualiza los datos de una instancia de RepOrdenPagoModel.
     *
     * @param RepOrdenPagoModel $repOrdenPago La instancia de RepOrdenPagoModel a actualizar.
     * @param array $data Los nuevos datos para actualizar la instancia.
     * @return bool Verdadero si la actualización fue exitosa, falso en caso contrario.
     */
    public function update(RepOrdenPagoModel $repOrdenPago, array $data): bool
    {
        return $repOrdenPago->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(RepOrdenPagoModel $repOrdenPago): bool
    {
        return $repOrdenPago->delete();
    }

    /**
     * Trunca la tabla rep_orden_pago
     *
     * @return bool
     * @throws \Exception
     */
    public function truncate(): bool
    {
        try {
            DB::connection($this->model->getConnectionName())->beginTransaction();

            $result = DB::connection($this->model->getConnectionName())
                ->statement('TRUNCATE TABLE suc.rep_orden_pago RESTART IDENTITY CASCADE');

            DB::connection($this->model->getConnectionName())->commit();

            return $result;
        } catch (\Exception $e) {
            DB::connection($this->model->getConnectionName())->rollBack();
            throw $e;
        }
    }
}
