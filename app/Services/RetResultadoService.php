<?php

namespace App\Services;

use App\Contracts\RetResultadoRepository;
use App\DTOs\RetResultadoDTO;
use App\Models\Suc\RetResultado;
use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;

class RetResultadoService
{
    private RetResultadoRepository $repository;

    public function __construct(RetResultadoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Calcula el retroactivo para un legajo específico.
     *
     * @param int $nroLegaj
     * @param int $nroCargoAnt
     * @param \DateTime $fechaRetDesde
     * @param Periodo $periodo
     * @return RetResultadoDTO|null
     */
    public function calcularRetroactivo(int $nroLegaj, int $nroCargoAnt, \DateTime $fechaRetDesde, Periodo $periodo): ?RetResultadoDTO
    {
        $retResultado = $this->repository->obtenerPorLlavePrimaria($nroLegaj, $nroCargoAnt, $fechaRetDesde, $periodo);

        if (!$retResultado) {
            return null;
        }

        // Aquí iría la lógica de cálculo del retroactivo
        // Por ejemplo:
        $montoTotal = $this->calcularMontoTotal($retResultado);

        return new RetResultadoDTO($retResultado, $montoTotal);
    }

    /**
     * Calcula el monto total del retroactivo.
     *
     * @param RetResultado $retResultado
     * @return float
     */
    private function calcularMontoTotal(RetResultado $retResultado): float
    {
        // Aquí iría la lógica de cálculo del monto total
        // Este es solo un ejemplo simplificado
        return $retResultado->c101_n + $retResultado->c103_n + $retResultado->c106_n;
    }

    /**
     * Crea un nuevo resultado de retroactivo.
     *
     * @param array $datos
     * @return RetResultado
     */
    public function crearRetResultado(array $datos): RetResultado
    {
        // Aquí podrías agregar validaciones o lógica de negocio antes de crear
        return $this->repository->crear($datos);
    }

    /**
     * Actualiza un resultado de retroactivo existente.
     *
     * @param RetResultado $retResultado
     * @param array $datos
     * @return bool
     */
    public function actualizarRetResultado(RetResultado $retResultado, array $datos): bool
    {
        // Aquí podrías agregar validaciones o lógica de negocio antes de actualizar
        return $this->repository->actualizar($retResultado, $datos);
    }

    /**
     * Obtiene todos los resultados de retroactivo para un tipo específico.
     *
     * @param TipoRetro $tipoRetro
     * @return array
     */
    public function obtenerPorTipoRetro(TipoRetro $tipoRetro): array
    {
        $resultados = $this->repository->obtenerPorTipoRetro($tipoRetro);
        return $resultados->map(function ($resultado) {
            return new RetResultadoDTO($resultado, $this->calcularMontoTotal($resultado));
        })->toArray();
    }
}
