<?php

namespace App\Services;

use App\Contracts\EmployeeRepositoryInterface;
use App\DTOs\EmployeeInfoDTO;
use App\Repositories\EmployeeRepository;

class EmployeeService
{
    private $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Busca un empleado por su número de documento y devuelve un DTO con su información.
     *
     * @param string $dni Número de documento del empleado a buscar.
     * @return EmployeeInfoDTO|null Objeto DTO con la información del empleado, o null si no se encuentra.
     */
    public function searchEmployee(string $dni): ?EmployeeInfoDTO
    {
        $employee = $this->employeeRepository->findByDni($dni);
        if (!$employee) {
            return null;
        }

        return new EmployeeInfoDTO(
            $employee->desc_nombr,
            $employee->desc_appat . ' ' . $employee->desc_apmat,
            $employee->nro_legaj,
            $employee->nro_docum,
            $this->employeeRepository->getFirstEmploymentDate($employee->nro_legaj)
        );
    }

    /**
     * Obtiene los cargos asociados a un empleado por su número de legajo.
     *
     * @param string $nroLegaj Número de legajo del empleado.
     * @return array Arreglo con los cargos del empleado.
     */
    public function getCargos(string $nroLegaj): array
    {
        return $this->employeeRepository->getCargos($nroLegaj);
    }
}
