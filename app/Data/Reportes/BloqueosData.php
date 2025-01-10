<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BloqueosData extends Data
{
    public function __construct(
        public readonly Carbon $fecha_registro,
        #[MapName('email')]
        public readonly string $correo_electronico,
        public readonly string $nombre,
        #[MapName('usuario_mapuche')]
        public readonly string $usuario_mapuche_solicitante,
        public readonly string $dependencia,
        #[MapName('nro_legaj')]
        public readonly int $legajo,
        #[MapName('nro_cargo')]
        public readonly int $n_de_cargo,
        #[MapName('fecha_baja')]
        public readonly ?Carbon $fecha_de_baja,
        #[MapName('tipo')]
        public readonly string $tipo_de_movimiento,
        public readonly ?string $observaciones,
        public readonly bool $chkstopliq,
        public readonly int $nro_liqui,
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
            'fecha_de_baja' => ['required_if:tipo_de_movimiento,Fallecido,Renuncia', 'date'],
        ];
    }

    public static function fromExcelRow(array $row, int $nroLiqui): self
    {
        $tipoMovimiento = strtolower($row['tipo_de_movimiento']);

        
        return new self(
            fecha_registro: Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hora_de_finalizacion'])),
            correo_electronico: strtolower(trim($row['correo_electronico'])),
            nombre: ucwords(strtolower(trim($row['nombre']))),
            usuario_mapuche_solicitante: strtolower(trim($row['usuario_mapuche_solicitante'])),
            dependencia: trim($row['dependencia']),
            legajo: (int)$row['legajo'],
            n_de_cargo: (int)$row['n_de_cargo'],
            fecha_de_baja: self::processFechaBaja($row, $tipoMovimiento),
            tipo_de_movimiento: $tipoMovimiento,
            observaciones: trim($row['observaciones'] ?? ''),
            chkstopliq: $tipoMovimiento === 'Licencia',
            nro_liqui: $nroLiqui
        );
    }

    private static function processFechaBaja(array $row, string $tipo): ?Carbon
    {

        if (!in_array($tipo, ['fallecido', 'renuncia']) || empty($row['fecha_de_baja'])) {
            return null;
        }

        $fecha = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_baja']));

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
