<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Data\RepOrdenPagoDtoData;
use App\Models\Reportes\RepOrdenPagoModel;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Proporciona una capa de servicio para administrar registros de RepOrdenPago.
 *
 *
 * Esta clase de servicio proporciona métodos para interactuar con los registros de RepOrdenPago, incluidos:
 *  recuperar todos los registros, recuperar un registro por su nro_liqui, crear un nuevo registro,
 *  actualizar un registro existente y eliminar un registro.
 *
 * La clase de servicio utiliza una clase de repositorio para manejar la lógica de acceso a datos.
 */
class RepOrdenPagoService
{
    /**
     * Crear una nueva instancia.
     */
    public function __construct(protected RepOrdenPagoRepositoryInterface $repository)
    {
    }

    /**
     * Obtiene todos los registros de RepOrdenPago.
     */
    public function getAllRepOrdenPago(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Obtiene RepOrdenPago por nro_liqui.
     *
     *
     */
    public function getRepOrdenPagoByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return $this->repository->getByNroLiqui($nroLiqui);
    }

    /**
     * Crea un nuevo registro de RepOrdenPago.
     *
     * @param RepOrdenPagoDtoData $data DTO con los datos para crear la orden de pago
     */
    public function createRepOrdenPago(RepOrdenPagoDtoData $data): RepOrdenPagoModel
    {
        try {
            return $this->repository->create($data->toArray());
        } catch (Exception $e) {
            Log::error('Error al crear orden de pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza un registro existente de RepOrdenPago.
     *
     *
     */
    public function updateRepOrdenPago(RepOrdenPagoModel $repOrdenPago, RepOrdenPagoDtoData $data): bool
    {
        return $this->repository->update($repOrdenPago, $data->toArray());
    }

    /**
     * Elimina un registro de RepOrdenPago.
     *
     *
     */
    public function deleteRepOrdenPago(RepOrdenPagoModel $repOrdenPago): bool
    {
        return $this->repository->delete($repOrdenPago);
    }

    /**
     * Garantiza que la tabla y la función almacenada estén presentes.
     */
    public function ensureTableAndFunction(): void
    {
        $this->repository->createTableIfNotExists();
        $this->repository->ensureStoredProcedure();
    }

    /**
     * Genera un reporte para las liquidaciones proporcionadas.
     *
     *
     */
    public function generateReport(array $liquidaciones): void
    {
        try {
            $this->repository->executeStoredProcedure($liquidaciones);
            Log::info('Reporte generado exitosamente para liquidaciones: ' . implode(',', $liquidaciones));
        } catch (Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trunca la tabla rep_orden_pago.
     *
     * @throws Exception
     */
    public function truncateTable(): bool
    {
        try {
            $result = $this->repository->truncate();
            Log::info('Tabla suc.rep_orden_pago truncada exitosamente');
            return $result;
        } catch (Exception $e) {
            Log::error('Error al truncar tabla suc.rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }
}
