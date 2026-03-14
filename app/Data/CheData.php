<?php

declare(strict_types=1);

namespace App\Data;

use Override;
use Spatie\LaravelData\Data;

/**
 * DTO para el contenido del archivo CHE (liquidación, neto y aportes/retenciones).
 */
class CheData extends Data
{
    public const ACCION_OBRERO = 'O';

    public function __construct(
        public readonly string $netoLiquidado,
        public readonly string $accion,
        /** @var array<int, CheGrupoAporteRetencionData> */
        public readonly array $grupoAportesRetenciones,
    ) {}

    /**
     * Convierte el DTO al formato esperado por la API de Pilaga y por persistencia.
     *
     * @return array{neto_liquidado: string, accion: string, grupo_aportes_retenciones: array<int, array{codigo: string, descripcion: string, importe: string}>}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'neto_liquidado' => $this->netoLiquidado,
            'accion' => $this->accion,
            'grupo_aportes_retenciones' => array_map(
                static fn (CheGrupoAporteRetencionData $item): array => $item->toArray(),
                $this->grupoAportesRetenciones,
            ),
        ];
    }

    /**
     * Array para persistencia en BD (grupo_aportes_retenciones como array, Eloquent lo casta a JSON).
     *
     * @return array{nro_liqui?: int, neto_liquidado: string, accion: string, grupo_aportes_retenciones: array}
     */
    public function toDatabaseArray(?int $nroLiqui = null): array
    {
        $payload = $this->toArray();
        $data = [
            'neto_liquidado' => $payload['neto_liquidado'],
            'accion' => $payload['accion'],
            'grupo_aportes_retenciones' => $payload['grupo_aportes_retenciones'],
        ];

        if ($nroLiqui !== null) {
            $data['nro_liqui'] = $nroLiqui;
        }

        return $data;
    }
}
