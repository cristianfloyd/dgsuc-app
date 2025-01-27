<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BloqueosData extends Data
{
    public function __construct(
        public readonly Carbon $fecha_registro,
        public readonly string $email,
        public readonly string $nombre,
        public readonly string $usuario_mapuche,
        public readonly string $dependencia,
        #[MapName('nro_legaj')]
        public readonly int $legajo,
        #[MapName('nro_cargo')]
        public readonly int $n_de_cargo,
        #[MapName('fecha_baja')]
        public readonly ?Carbon $fecha_de_baja,
        public readonly string $tipo,
        public readonly ?string $observaciones,
        public readonly bool $chkstopliq,
        public readonly int $nro_liqui,
        public readonly BloqueosEstadoEnum $estado = BloqueosEstadoEnum::PENDIENTE,
        public readonly ?string $mensaje_error = null,
    ) {}

    public static function rules(ValidationContext $context = null): array
    {
        return [
            'correo_electronico' => ['required', 'email'],
            'nombre' => ['required', 'string'],
            'usuario_mapuche_solicitante' => ['required', 'string'],
            'dependencia' => ['required', 'string'],
            'legajo' => [
                'required',
                'numeric',
            ],
            'n_de_cargo' => [
                'required',
                'numeric',
                Rule::unique('pgsql-mapuche.suc.rep_bloqueos_import', 'nro_cargo')
            ],
            'tipo_de_movimiento' => ['required', 'string', 'in:Licencia,Fallecido,Renuncia'],
            'fecha_de_baja' => [
                'required_if:tipo_de_movimiento,Fallecido,Renuncia',
                function ($attribute, $value, $fail) {
                    // Permitir tanto fechas como números (formato Excel)
                    if (!empty($value) && !is_numeric($value) && !strtotime($value)) {
                        $fail('El formato de fecha no es válido.');
                    }
                }
            ],
        ];
    }

    public static function fromExcelRow(array $row, int $nroLiqui): self
    {
        $tipoMovimiento = strtolower($row['tipo_de_movimiento']);


        return new self(
            fecha_registro: Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hora_de_finalizacion'])),
            email: strtolower(trim($row['correo_electronico'])),
            nombre: ucwords(strtolower(trim($row['nombre']))),
            usuario_mapuche: strtolower(trim($row['usuario_mapuche_solicitante'])),
            dependencia: trim($row['dependencia']),
            legajo: (int)$row['legajo'],
            n_de_cargo: (int)$row['n_de_cargo'],
            fecha_de_baja: self::processFechaBaja($row, $tipoMovimiento),
            tipo: $tipoMovimiento,
            observaciones: trim($row['observaciones'] ?? ''),
            chkstopliq: $tipoMovimiento === 'Licencia',
            nro_liqui: $nroLiqui
        );
    }

    public static function fromValidatedData(array $validatedData, int $nroLiqui): self
    {
        $instance = new self(
            fecha_registro: now(),
            email: strtolower($validatedData['correo_electronico']),
            nombre: ucwords(strtolower($validatedData['nombre'])),
            usuario_mapuche: strtolower($validatedData['usuario_mapuche_solicitante']),
            dependencia: $validatedData['dependencia'],
            legajo: $validatedData['legajo'],
            n_de_cargo: $validatedData['n_de_cargo'],
            fecha_de_baja: $validatedData['fecha_de_baja'],
            tipo: strtolower($validatedData['tipo_de_movimiento']),
            observaciones: $validatedData['observaciones'] ?? '',
            chkstopliq: strtolower($validatedData['tipo_de_movimiento']) === 'licencia',
            nro_liqui: $nroLiqui,
            estado: $validatedData['estado'],
            mensaje_error: $validatedData['mensaje_error'] ?? null
        );

        Log::debug('DTO creado:', [
            'instance' => $instance,
        ]);

        return $instance;
    }

    private static function processFechaBaja(array $row, string $tipo): ?Carbon
    {

        // Si no es un tipo que requiera fecha, retornamos null
        if (!in_array($tipo, ['fallecido', 'renuncia'])) {
            return null;
        }

        // Si no hay fecha y el tipo la requiere, esto se manejará en las reglas de validación
        if (empty($row['fecha_de_baja'])) {
            return null;
        }

        // Procesamiento de la fecha según el formato
        $fecha = is_numeric($row['fecha_de_baja'])
            ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_baja']))
            : Carbon::parse($row['fecha_de_baja']);

        // Aplicamos la lógica de negocio para ajustar la fecha
        return $fecha->day === 1
            ? $fecha->subMonth()->endOfMonth()
            : $fecha;
    }

    /**
     * Método helper para validar la combinación legajo-cargo
     */
    public function validarCombinacionLegajoCargo(): bool
    {
        return Dh03::validarParLegajoCargo($this->legajo, $this->n_de_cargo);
    }

    /**
     * Obtiene detalles extendidos de la validación
     */
    public function getDetallesValidacion(): array
    {
        return Dh03::getDetallesValidacion($this->legajo, $this->n_de_cargo);
    }
}
