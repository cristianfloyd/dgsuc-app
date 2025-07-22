<?php

namespace App\Data\Mapuche;

use App\Models\Dh01;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class AgenteData extends Data
{
    public function __construct(
        public string $nombre,
        public string $apellido,
        public string $nroLegaj,
        #[Size(8)]
        public string $dni,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $fechaInicio,
    ) {
    }

    // Validación integrada
    public static function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'nroLegaj' => ['required', 'string'],
            'dni' => ['required', 'string', 'size:8'],
            'fechaInicio' => ['nullable', 'date'],
        ];
    }

    // Transformación desde un modelo
    public static function fromModel(Dh01 $employee): self
    {
        return new self(
            nombre: $employee->desc_nombr,
            apellido: trim($employee->desc_appat . ' ' . $employee->desc_apmat),
            nroLegaj: $employee->nro_legaj,
            dni: $employee->nro_docum,
            fechaInicio: Carbon::parse($employee->dh03()->orderBy('fec_alta', 'asc')->value('fec_alta')),
        );
    }

    // Método helper para crear una colección de agentes
    public static function collection(Collection $employees): Collection
    {
        return $employees->map(fn (Dh01 $employee) => self::fromModel($employee));
    }

    // Método para obtener el nombre completo
    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // Método para verificar si el agente tiene fecha de inicio
    public function tieneInicio(): bool
    {
        return $this->fechaInicio !== null;
    }
}
