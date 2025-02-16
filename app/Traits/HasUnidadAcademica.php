<?php

namespace App\Traits;

use App\Data\UnidadAcademicaData;

trait HasUnidadAcademica
{

    /**
     * Determina los códigos de la unidad académica basado en el código proporcionado.
     *
     * Si el código es válido, actualiza los atributos 'domicilio' y 'actividad' con los valores correspondientes.
     *
     * @param ?string $codigo El código de la unidad académica a determinar.
     */
    public function determinarCodigosUnidadAcademica(?string $codigo): void
    {
        if (!$codigo) return;

        $unidad = UnidadAcademicaData::fromCodigo($codigo);

        if ($unidad) {
            $this->attributes['domicilio'] = $unidad->sucursal;
            $this->attributes['actividad'] = $unidad->actividad->value;
        }
    }

    /**
     * Obtiene la unidad académica actual
     */
    public function getUnidadAcademica(): ?UnidadAcademicaData
    {
        // Intentamos obtener la unidad a partir del código original
        return $this->original['domicilio'] ?
            UnidadAcademicaData::fromCodigo($this->original['domicilio']) :
            null;
    }
}
