<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use App\Models\Reportes\BloqueosDataModel;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BloqueoProcesadoData extends Data
{
    public function __construct(
        #[Required]
        public readonly bool $success,

        #[Required]
        #[StringType]
        public readonly string $message,

        public readonly ?BloqueosDataModel $bloqueo,

        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly ?Carbon $processed_at,

        public readonly ?array $metadata = []
    ) {}

    public static function fromError(string $message, BloqueosDataModel $bloqueo): self
    {
        return new self(
            success: false,
            message: $message,
            bloqueo: $bloqueo,
            processed_at: now()
        );
    }

    public static function fromSuccess(BloqueosDataModel $bloqueo): self
    {
        return new self(
            success: true,
            message: 'Procesado exitosamente',
            bloqueo: $bloqueo,
            processed_at: now()
        );
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'bloqueo.nro_cargo' => ['required', 'integer'],
            'bloqueo.nro_legaj' => ['required', 'integer'],
            'bloqueo.fecha_baja' => ['required', 'date'],
            'bloqueo.tipo' => ['required', 'string', 'in:Licencia,Fallecido,Renuncia'],
        ];
    }

    public function transform(\Spatie\LaravelData\Support\Transformation\TransformationContext|\Spatie\LaravelData\Support\Transformation\TransformationContextFactory|null $transformationContext = null): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'cargo_id' => $this->bloqueo?->nro_cargo,
            'legajo' => $this->bloqueo?->nro_legaj,
            'tipo_bloqueo' => $this->bloqueo?->tipo,
            'fecha_proceso' => $this->processed_at?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata
        ];
    }

    public function toResponse($request)
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'cargo_id' => $this->bloqueo?->nro_cargo,
            'legajo' => $this->bloqueo?->nro_legaj,
            'tipo_bloqueo' => $this->bloqueo?->tipo,
            'fecha_baja' => $this->bloqueo?->fecha_baja,
            'fecha_proceso' => $this->processed_at?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata
        ];
    }

    public static function fromModel(BloqueosDataModel $bloqueo): self
    {
        return new self(
            success: true,
            message: 'Bloqueo procesado',
            bloqueo: $bloqueo,
            processed_at: now(),
            metadata: [
                'tipo_operacion' => $bloqueo->tipo,
                'estado_anterior' => $bloqueo->cargo?->chkstopliq,
                'fecha_proceso' => now()->toDateTimeString()
            ]
        );
    }

    public function toResource(): array
    {
        return [
            'id' => $this->bloqueo?->id,
            'legajo' => $this->bloqueo?->nro_legaj,
            'cargo' => $this->bloqueo?->nro_cargo,
            'tipo_bloqueo' => $this->bloqueo?->tipo,
            'estado' => $this->success ? 'Procesado' : 'Error',
            'fecha_proceso' => $this->processed_at,
            'resultado' => $this->message
        ];
    }
}
