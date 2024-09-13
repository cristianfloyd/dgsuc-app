<?php

namespace App\Repositories;

use App\Models\Reportes\RepOrdenPagoModel;
use App\Contracts\RepOrdenPagoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RepOrdenPagoRepository implements RepOrdenPagoRepositoryInterface
{
    /**
     * Obtiene todas las instancias de RepOrdenPagoModel.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): Collection
    {
        return RepOrdenPagoModel::all();
    }


    /**
     * Obtiene la primera instancia de RepOrdenPagoModel que coincida con el número de liquidación proporcionado.
     *
     * @param int $nroLiqui El número de liquidación a buscar.
     * @return \App\Models\Reportes\RepOrdenPagoModel|null La primera instancia de RepOrdenPagoModel que coincida con el número de liquidación, o null si no se encuentra ninguna.
     */
    public function getByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return RepOrdenPagoModel::where('nro_liqui', $nroLiqui)->first();
    }


    /**
     * Crea una nueva instancia de RepOrdenPagoModel con los datos proporcionados.
     *
     * @param array $data Los datos para crear la nueva instancia.
     * @return \App\Models\Reportes\RepOrdenPagoModel La nueva instancia creada.
     */
    public function create(array $data): RepOrdenPagoModel
    {
        return RepOrdenPagoModel::create($data);
    }


    /**
     * Actualiza los datos de una instancia de RepOrdenPagoModel.
     *
     * @param \App\Models\Reportes\RepOrdenPagoModel $repOrdenPago La instancia de RepOrdenPagoModel a actualizar.
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
}
