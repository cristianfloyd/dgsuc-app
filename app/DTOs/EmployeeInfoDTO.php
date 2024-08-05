<?php

namespace App\DTOs;

/**
 * Representa la información de un empleado.
 *
 * @property string $nombre El nombre del empleado.
 * @property string $apellido El apellido del empleado.
 * @property string $nroLegaj El número de legajo del empleado.
 * @property string $dni El número de documento de identidad del empleado.
 * @property string|null $fechaInicio La fecha de inicio del empleado, o null si no se conoce.
 */
class EmployeeInfoDTO
{
    /**
     * Construye un objeto DTO que representa la información de un empleado.
     *
     * @param string $nombre El nombre del empleado.
     * @param string $apellido El apellido del empleado.
     * @param string $nroLegaj El número de legajo del empleado.
     * @param string $dni El número de documento de identidad del empleado.
     * @param string|null $fechaInicio La fecha de inicio del empleado, o null si no se conoce.
     */
    public function __construct(
        public string $nombre,
        public string $apellido,
        public string $nroLegaj,
        public string $dni,
        public ?string $fechaInicio
    ) {}
}
