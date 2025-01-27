<?php

namespace App\Services\Validation;

use App\Enums\BloqueosEstadoEnum;
use App\Services\DateParserService;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelRowValidationService
{
    private array $errors = [];


    public function __construct(
        private readonly DateParserService $dateParserService
    ) {}

    /**
     * Valida y normaliza una fila completa del Excel
     */
    public function validateRow(array $row): array
    {
        $this->errors = [];
        Log::debug('Iniciando validación de fila', ['row' => $row]);

        // Parseamos la fecha de la misma manera que en validateFechaBaja
        $fechaBaja = isset($row['fecha_de_baja'])
        ? Date::excelToDateTimeObject($row['fecha_de_baja'])
        : null;
        $fechaBaja = $this->dateParserService->parseDate($fechaBaja);

        // Si el registro ya fue marcado como duplicado, mantenemos ese estado
        if (isset($row['estado']) && $row['estado'] === BloqueosEstadoEnum::DUPLICADO) {
            Log::debug('Registro duplicado, manteniendo estado', ['row' => $row]);
            return [
                'correo_electronico' => $row['correo_electronico'],
                'nombre' => $row['nombre'],
                'usuario_mapuche_solicitante' => $row['usuario_mapuche_solicitante'],
                'dependencia' => $row['dependencia'],
                'legajo' => $row['legajo'],
                'n_de_cargo' => $row['n_de_cargo'],
                'tipo_de_movimiento' => $row['tipo_de_movimiento'],
                'fecha_de_baja' => $fechaBaja ?? null,
                'observaciones' => $row['observaciones'] ?? '',
                'estado' => BloqueosEstadoEnum::DUPLICADO,
                'mensaje_error' => "Cargo duplicado: {$row['n_de_cargo']}"
            ];
        }

        // Ejecutamos todas las validaciones
        $legajoValidation = $this->validateLegajo($row['legajo']);
        $cargoValidation = $this->validateCargo($row['n_de_cargo']);
        $tipoValidation = $this->validateTipoMovimiento($row['tipo_de_movimiento']);
        $fechaValidation = $this->validateFechaBaja($row['fecha_de_baja'] ?? null, $row['tipo_de_movimiento']);
        $usuarioMapucheValidation = $this->validateUsuarioMapuche($row['usuario_mapuche_solicitante']);


        $validatedData = [
            'correo_electronico' => $this->validateEmail($row['correo_electronico']),
            'nombre' => $this->validateNombre($row['nombre']),
            'usuario_mapuche_solicitante' => $usuarioMapucheValidation['value'],
            'dependencia' => $this->validateDependencia($row['dependencia']),
            'legajo' => $legajoValidation['value'],
            'n_de_cargo' => $cargoValidation['value'],
            'tipo_de_movimiento' => $tipoValidation['value'],
            'fecha_de_baja' => $fechaValidation['value'],
            'observaciones' => $this->validateObservaciones($row['observaciones']),
            'estado' => BloqueosEstadoEnum::VALIDADO,
            'mensaje_error' => null,
        ];

        $validations = [
            $usuarioMapucheValidation,
            $legajoValidation,
            $cargoValidation,
            $fechaValidation,
            $tipoValidation,
        ];

        // Verificamos si alguna validación falló
        foreach ($validations as $validation) {
            if ($validation['estado'] === BloqueosEstadoEnum::ERROR_VALIDACION) {
                $validatedData['estado'] = BloqueosEstadoEnum::ERROR_VALIDACION;
                $validatedData['mensaje_error'] = $validation['mensaje_error'];
                return $validatedData;
            }
        }

        return $validatedData;
    }



    private function addError(string $field, string $message): void
    {
        $this->errors[] = "{$field}: {$message}";
    }

    /**
     * Valida y normaliza el email
     */
    private function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw (new ValidationException('El email es requerido'))
                ->setField('email')
                ->addError('email', 'Campo requerido');
        }

        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw (new ValidationException("Email inválido: {$email}"))
                ->setField('email')
                ->setContext(['input' => $email]);
        }

        return $email;
    }

    /**
     * Valida y normaliza el nombre
     */
    private function validateNombre(?string $nombre): string
    {
        if (empty($nombre)) {
            throw new ValidationException('El nombre es requerido');
        }

        $nombre = trim($nombre);

        if (strlen($nombre) < 2) {
            throw new ValidationException('El nombre debe tener al menos 2 caracteres');
        }

        return ucwords(strtolower($nombre));
    }

    /**
     * Valida y normaliza el usuario Mapuche
     */
    private function validateUsuarioMapuche(?string $usuario): array
    {
        if (empty($usuario)) {
            return [
                'value' => null,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => 'El usuario Mapuche es requerido'
            ];
        }

        $usuario = strtolower(trim($usuario));

        if (!preg_match('/^[a-z0-9._-]+$/', $usuario)) {
            return [
                'value' => $usuario,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Usuario Mapuche inválido: {$usuario}"
            ];
        }

        return [
            'value' => $usuario,
            'estado' => BloqueosEstadoEnum::VALIDADO
        ];
    }


    /**
     * Valida y normaliza la dependencia
     */
    private function validateDependencia(?string $dependencia): string
    {
        if (empty($dependencia)) {
            throw new ValidationException('La dependencia es requerida');
        }

        return trim($dependencia);
    }


    /**
     * Valida y normaliza el legajo.
     *
     * Verifica si el legajo es numérico y mayor que 0. Si no cumple con estas condiciones,
     * devuelve un arreglo con el estado de error de validación y un mensaje de error.
     * Si el legajo es válido, devuelve un arreglo con el estado de validado.
     *
     * @param mixed $legajo El legajo a validar.
     * @return array Un arreglo con el valor del legajo, el estado de validación y un mensaje de error si corresponde.
     */
    private function validateLegajo($legajo): array
    {
        if (!is_numeric($legajo)) {
            return [
                'value' => $legajo,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Legajo debe ser numérico: {$legajo}"
            ];
        }

        $legajo = (int)$legajo;

        if ($legajo < 1) {
            return [
                'value' => $legajo,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Legajo inválido: {$legajo}"
            ];
        }

        return [
            'value' => $legajo,
            'estado' => BloqueosEstadoEnum::VALIDADO
        ];
    }

    /**
     * Valida y normaliza el número de cargo
     */
    private function validateCargo($cargo): array
    {
        if (!is_numeric($cargo)) {
            return [
                'value' => $cargo,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Cargo debe ser numérico: {$cargo}"
            ];
        }

        $cargo = (int)$cargo;

        if ($cargo < 1) {
            return [
                'value' => $cargo,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Cargo inválido: {$cargo}"
            ];
        }

        return [
            'value' => $cargo,
            'estado' => BloqueosEstadoEnum::VALIDADO
        ];
    }

    /**
     * Valida y normaliza el tipo de movimiento
     */
    private function validateTipoMovimiento(?string $tipo): array
    {
        if (empty($tipo)) {
            return [
                'value' => null,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => 'El tipo de movimiento es requerido'
            ];
        }

        $tipo = strtolower(trim($tipo));
        $tiposValidos = ['licencia', 'fallecido', 'renuncia'];

        if (!in_array($tipo, $tiposValidos)) {
            return [
                'value' => $tipo,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Tipo de movimiento inválido: {$tipo}"
            ];
        }

        return [
            'value' => $tipo,
            'estado' => BloqueosEstadoEnum::VALIDADO
        ];
    }

    /**
     * Valida y normaliza la fecha de baja
     */
    private function validateFechaBaja($fecha, string $tipoMovimiento): array
    {
        // Si es licencia, la fecha no es requerida
        if (strtolower($tipoMovimiento) === 'licencia') {
            return [
                'value' => null,
                'estado' => BloqueosEstadoEnum::VALIDADO
            ];
        }

        // Para otros tipos, la fecha es obligatoria
        if (empty($fecha)) {
            return [
                'value' => null,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => 'La fecha de baja es requerida para este tipo de movimiento'
            ];
        }

        try {
            // Si es un número, asumimos que es fecha de Excel
            if (is_numeric($fecha)) {
                $fecha = Date::excelToDateTimeObject($fecha);
            }

            // Parseamos la fecha utilizando el servicio de análisis de fechas
            $fechaBaja = $this->dateParserService->parseDate($fecha);

            if ($fechaBaja->isFuture()) {
                return [
                    'value' => $fechaBaja,
                    'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                    'mensaje_error' => 'La fecha de baja no puede ser futura'
                ];
            }

            // Si es día 1, ajustamos al último día del mes anterior
            $fechaAjustada = $fechaBaja->day === 1
                ? $fechaBaja->subMonth()->endOfMonth()
                : $fechaBaja;

            return [
                'value' => $fechaAjustada,
                'estado' => BloqueosEstadoEnum::VALIDADO
            ];
        } catch (\Exception $e) {
            return [
                'value' => $fecha,
                'estado' => BloqueosEstadoEnum::ERROR_VALIDACION,
                'mensaje_error' => "Error en fecha de baja: {$e->getMessage()}"
            ];
        }
    }

    /**
     * Valida y normaliza las observaciones
     */
    private function validateObservaciones(?string $observaciones): string
    {
        return trim($observaciones ?? '');
    }
}
