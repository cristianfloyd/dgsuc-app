<?php

namespace App\Data\Mapuche;

use App\Models\Dh01;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class AgenteData extends Data
{
    public function __construct(
        public string $nombre,
        public string $apellido,
        public int $nroLegaj,
        #[Size(8)]
        public string $dni,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $fechaInicio,
    ) {
    }

    /**
     * Define las reglas de validación para los datos del agente.
     *
     * @param ValidationContext|null $context Contexto de validación opcional.
     *
     * @return array<string, array<int, string>> Un array asociativo donde las claves son los nombres de los campos
     *                                            y los valores son arrays de reglas de validación.
     */
    public static function rules(ValidationContext $context = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'nroLegaj' => ['required', 'string'],
            'dni' => ['required', 'string', 'size:8'],
            'fechaInicio' => ['nullable', 'date'],
        ];
    }

    /**
     * Crea una instancia de AgenteData a partir de un modelo Dh01.
     *
     * @param Dh01 $employee El modelo Dh01 del empleado.
     *
     * @return self Una nueva instancia de AgenteData.
     */
    public static function fromModel(Dh01 $employee): self
    {
        return new self(
            nombre: $employee->desc_nombr,
            apellido: trim((string) $employee->desc_appat . ' ' . $employee->desc_apmat),
            nroLegaj: $employee->nro_legaj,
            dni: $employee->nro_docum,
            fechaInicio: Carbon::parse($employee->dh03()->orderBy('fec_alta', 'asc')->value('fec_alta')),
        );
    }

    /**
     * Crea una colección de AgenteData a partir de una colección de modelos Dh01.
     *
     * @param Collection<int, Dh01> $employees Colección de modelos Dh01.
     *
     * @return Collection<int, AgenteData> Colección de instancias AgenteData.
     */
    public static function collection(Collection $employees): Collection
    {
        return $employees->map(fn (Dh01 $employee): \App\Data\Mapuche\AgenteData => self::fromModel($employee));
    }

    /**
     * Obtiene el nombre completo del agente.
     *
     * @return string El nombre y apellido concatenados.
     */
    public function nombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Verifica si el agente tiene una fecha de inicio válida.
     *
     * @return bool Verdadero si fechaInicio es una instancia de Carbon, falso en caso contrario.
     */
    public function tieneInicio(): bool
    {
        return $this->fechaInicio instanceof Carbon;
    }
}
