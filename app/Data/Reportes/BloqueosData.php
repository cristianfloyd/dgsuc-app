<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BloqueosData extends Data
{
    public function __construct(
        public readonly Carbon $fecha_registro,
        public readonly string $email,
        public readonly string $nombre,
        public readonly string $usuario_mapuche,
        public readonly string $dependencia,
        public readonly int $nro_legaj,
        public readonly int $nro_cargo,
        public readonly ?Carbon $fecha_baja,
        public readonly string $tipo,
        public readonly ?string $observaciones,
        public readonly bool $chkstopliq,
        public readonly int $nro_liqui,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'email' => ['required', 'email'],
            'nombre' => ['required', 'string'],
            'usuario_mapuche' => ['required', 'string'],
            'dependencia' => ['required', 'string'],
            'nro_legaj' => ['required', 'numeric'],
            'nro_cargo' => ['required', 'numeric', 'unique:suc.rep_bloqueos_import'],
            'tipo' => ['required', 'string', 'in:licencia,fallecido,renuncia'],
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
            nro_legaj: (int)$row['legajo'],
            nro_cargo: (int)$row['n_de_cargo'],
            fecha_baja: self::processFechaBaja($row, $tipoMovimiento),
            tipo: $tipoMovimiento,
            observaciones: trim($row['observaciones'] ?? ''),
            chkstopliq: $tipoMovimiento === 'licencia',
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
}
