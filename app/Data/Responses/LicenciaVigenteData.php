<?php

namespace App\Data\Responses;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class LicenciaVigenteData extends Data
{
    /**
     * @param int $nro_legaj Número de legajo del agente
     * @param int|null $inicio Día de inicio de la licencia en el periodo
     * @param int|null $final Día final de la licencia en el periodo
     * @param bool $es_legajo Indica si la licencia está asociada directamente al legajo (true) o a un cargo (false)
     * @param int $condicion Código numérico que representa el tipo de condición/licencia
     * @param string|null $descripcion_licencia Descripción del tipo de licencia
     * @param Carbon|null $fecha_desde Fecha de inicio de la licencia
     * @param Carbon|null $fecha_hasta Fecha de finalización de la licencia
     * @param string|null $nro_cargo Número de cargo asociado (si es_legajo=false)
     * @param int|null $dias_totales Total de días de la licencia
     */
    public function __construct(
        public int $nro_legaj,
        public ?int $inicio,
        public ?int $final,
        public bool $es_legajo,
        public int $condicion,
        public ?string $descripcion_licencia = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $fecha_desde = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $fecha_hasta = null,
        public ?string $nro_cargo = null,
        public ?int $dias_totales = null,
    ) {
        // Calcular días totales si no se proporcionan
        if ($this->dias_totales === null && $this->inicio !== null && $this->final !== null) {
            $this->dias_totales = ($this->final - $this->inicio) + 1;
        }
    }

    /**
     * Obtiene la descripción legible del tipo de condición/licencia.
     */
    public function getDescripcionCondicion(): string
    {
        return match ($this->condicion) {
            5 => 'Maternidad',
            10 => 'Excedencia',
            11 => 'Maternidad Down',
            12 => 'Vacaciones',
            13 => 'Licencia Sin Goce de Haberes',
            18 => 'ILT Primer Tramo',
            19 => 'ILT Segundo Tramo',
            51 => 'Protección Integral',
            default => 'Otra Licencia',
        };
    }

    /**
     * Determina si la licencia es de maternidad.
     */
    public function esLicenciaMaternidad(): bool
    {
        return \in_array($this->condicion, [5, 11]);
    }

    /**
     * Determina si la licencia es por enfermedad.
     */
    public function esLicenciaEnfermedad(): bool
    {
        return \in_array($this->condicion, [18, 19]);
    }

    /**
     * Crear una colección tipada desde un conjunto de resultados.
     *
     * @param Collection|array $resultados
     */
    public static function fromResultados($resultados): DataCollection
    {
        if (\is_array($resultados)) {
            $resultados = collect($resultados);
        }

        return new DataCollection(
            LicenciaVigenteData::class,
            $resultados->map(fn ($row): \App\Data\Responses\LicenciaVigenteData => self::fromRow($row)),
        );
    }

    /**
     * Crear una instancia desde una fila de resultados.
     *
     * @param object|array $row
     */
    public static function fromRow($row): self
    {
        $row = (object) $row;

        return new self(
            nro_legaj: $row->nro_legaj,
            inicio: $row->inicio,
            final: $row->final,
            es_legajo: $row->es_legajo,
            condicion: $row->condicion,
            descripcion_licencia: $row->descripcion_licencia ?? null,
            fecha_desde: isset($row->fec_desde) ? Carbon::parse($row->fec_desde) : null,
            fecha_hasta: isset($row->fec_hasta) ? Carbon::parse($row->fec_hasta) : null,
            nro_cargo: $row->nro_cargo ?? null,
        );
    }

    /**
     * Convierte el DTO a un array para exportación Excel.
     */
    public function toExcelRow(): array
    {
        return [
            'Legajo' => $this->nro_legaj,
            'Tipo' => $this->es_legajo ? 'Legajo' : 'Cargo',
            'Cargo' => $this->nro_cargo,
            'Condición' => $this->getDescripcionCondicion(),
            'Inicio Periodo' => $this->inicio,
            'Fin Periodo' => $this->final,
            'Días' => $this->dias_totales,
            'Fecha Desde' => $this->fecha_desde?->format('d/m/Y'),
            'Fecha Hasta' => $this->fecha_hasta?->format('d/m/Y') ?? 'Sin definir',
        ];
    }
}
