<?php

namespace App\Data;

use App\Models\Dh90;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

/**
 * Data Object para la tabla dh90.
 */
class Dh90Data extends Data
{
    /**
     * Crea una nueva instancia del DTO.
     *
     * @param int $nroCargo
     * @param int|null $nroCargoasociado
     * @param string|null $tipoasociacion
     */
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public int $nroCargo,

        #[IntegerType, Min(1)]
        public ?int $nroCargoasociado = null,

        #[Size(1)]
        public ?string $tipoasociacion = null,
    ) {
    }

    /**
     * Convierte el DTO a un modelo Eloquent.
     *
     * @return \App\Models\Dh90
     */
    public function toModel(): Dh90
    {
        return new Dh90([
            'nro_cargo' => $this->nroCargo,
            'nro_cargoasociado' => $this->nroCargoasociado,
            'tipoasociacion' => $this->tipoasociacion,
        ]);
    }

    /**
     * Actualiza un modelo existente con los datos del DTO.
     *
     * @param Dh90 $model
     * @return Dh90
     */
    public function updateModel(Dh90 $model): Dh90
    {
        $model->nro_cargo = $this->nroCargo;
        $model->nro_cargoasociado = $this->nroCargoasociado;
        $model->tipoasociacion = $this->tipoasociacion;

        return $model;
    }
}
