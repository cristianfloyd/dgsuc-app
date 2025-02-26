<?php

namespace App\Data\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use Spatie\LaravelData\Data;
use App\Rules\LegajoCargoExistsRule;
use App\Models\Reportes\BloqueosDataModel;
use Spatie\LaravelData\Attributes\WithCast;
use Illuminate\Validation\ValidationException;
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

        public readonly ?array $metadata = [],

        public bool $cambiosRealizados = false,

        public array $datosOriginales = []
    ) {}

    public static function fromError(string $message, BloqueosDataModel $bloqueo, array $metadata = []): self
    {
        return new self(
            success: false,
            message: $message,
            bloqueo: $bloqueo,
            processed_at: now(),
            metadata: array_merge($metadata, [
                'validacion' => Dh03::getDetallesValidacion(
                    $bloqueo->nro_legaj,
                    $bloqueo->nro_cargo
                )
            ])
        );
    }

    public static function fromSuccess(BloqueosDataModel $bloqueo): self
    {
        if (!Dh03::validarParLegajoCargo($bloqueo->nro_legaj, $bloqueo->nro_cargo)) {
            throw ValidationException::withMessages([
                'legajo_cargo' => 'Combinación legajo-cargo inválida'
            ]);
        }

        return new self(
            success: true,
            message: 'Procesado exitosamente',
            bloqueo: $bloqueo,
            processed_at: now(),
            metadata: [
                'validacion' => Dh03::getDetallesValidacion(
                    $bloqueo->nro_legaj,
                    $bloqueo->nro_cargo
                )
            ]
        );
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'bloqueo.nro_cargo' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($context) {
                    $nroLegaj = $context->payload['bloqueo']['nro_legaj'] ?? null;
                    if ($nroLegaj) {
                        $rule = new LegajoCargoExistsRule($nroLegaj, $value);
                        $rule->validate($attribute, $value, $fail);
                    }
                }
            ],
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
