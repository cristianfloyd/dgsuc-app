<?php

namespace App\Repositories;

use App\Models\Dh01;
use App\Models\Dh03;
use App\Contracts\EmployeeRepositoryInterface;

/**
 * Implementa la interfaz EmployeeRepositoryInterface para proporcionar funcionalidad de repositorio para los empleados.
 */
class EmployeeRepository implements EmployeeRepositoryInterface
{
    /**
     * Busca un empleado por su número de documento.
     *
     * @param string $dni El número de documento del empleado.
     * @return Dh01|null El modelo de empleado si se encuentra, de lo contrario null.
     */
    public function findByDni(string $dni): ?Dh01
    {
        return Dh01::where('nro_docum', $dni)->first();
    }

    /**
     * Obtiene la fecha de primer empleo de un empleado basado en su número de legajo.
     *
     * @param string $nroLegaj El número de legajo del empleado.
     * @return string|null La fecha de primer empleo del empleado, o null si no se encuentra.
     */
    public function getFirstEmploymentDate(string $nroLegaj): ?string
    {
        return Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'asc')
            ->value('fec_alta');
    }

    /**
     * Obtiene un array con los cargos asociados a un número de legajo de empleado.
     *
     * @param string $nroLegaj El número de legajo del empleado.
     * @return array Un array con los detalles de los cargos del empleado, incluyendo el número de cargo, categoría, fechas de alta y baja, y estado de liquidación.
     */
    public function getCargos(string $nroLegaj): array
    {
        return Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'desc')
            ->get(['nro_cargo', 'codc_categ', 'fec_alta', 'fec_baja', 'vig_caano', 'vig_cames', 'chkstopliq'])
            ->toArray();
    }
}
