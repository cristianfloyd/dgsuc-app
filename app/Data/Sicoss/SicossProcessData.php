<?php

namespace App\Data\Sicoss;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class SicossProcessData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        public readonly int $nro_legaj,

        #[IntegerType]
        #[Min(0)]
        public readonly int $check_retro = 0,

        #[IntegerType]
        #[Min(0)]
        public readonly int $check_lic = 0,

        #[IntegerType]
        #[Min(0)]
        public readonly int $check_sin_activo = 0,

        #[IntegerType]
        #[Min(0)]
        public readonly int $seguro_vida_patronal = 0,

        public readonly ?float $TopeJubilatorioPatronal = null,

        public readonly ?float $TopeJubilatorioPersonal = null,

        public readonly ?float $TopeOtrosAportesPersonal = null,

        public readonly bool $truncaTope = false,

        public readonly ?int $nro_liqui = null
    ) {}

    /**
     * Crea una instancia desde un array de datos
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nro_legaj: $data['nro_legaj'],
            check_retro: $data['check_retro'] ?? 0,
            check_lic: $data['check_lic'] ?? 0,
            check_sin_activo: $data['check_sin_activo'] ?? 0,
            seguro_vida_patronal: $data['seguro_vida_patronal'] ?? 0,
            TopeJubilatorioPatronal: $data['TopeJubilatorioPatronal'] ?? null,
            TopeJubilatorioPersonal: $data['TopeJubilatorioPersonal'] ?? null,
            TopeOtrosAportesPersonal: $data['TopeOtrosAportesPersonal'] ?? null,
            truncaTope: $data['truncaTope'] ?? false,
            nro_liqui: $data['nro_liqui'] ?? null
        );
    }

    /**
     * Crea una nueva instancia con los topes por defecto aplicados
     */
    public function withDefaultTopes(array $topes): self
    {
        return new self(
            nro_legaj: $this->nro_legaj,
            check_retro: $this->check_retro,
            check_lic: $this->check_lic,
            check_sin_activo: $this->check_sin_activo,
            seguro_vida_patronal: $this->seguro_vida_patronal,
            TopeJubilatorioPatronal: $this->TopeJubilatorioPatronal ?? $topes['TopeJubilatorioPatronal'],
            TopeJubilatorioPersonal: $this->TopeJubilatorioPersonal ?? $topes['TopeJubilatorioPersonal'],
            TopeOtrosAportesPersonal: $this->TopeOtrosAportesPersonal ?? $topes['TopeOtrosAportesPersonal'],
            truncaTope: $this->truncaTope,
            nro_liqui: $this->nro_liqui
        );
    }

    /**
     * Verifica si todos los topes están configurados
     */
    public function hasAllTopes(): bool
    {
        return !is_null($this->TopeJubilatorioPatronal)
            && !is_null($this->TopeJubilatorioPersonal)
            && !is_null($this->TopeOtrosAportesPersonal);
    }

    /**
     * Convierte a array para compatibilidad con código legacy
     */
    public function toArray(): array
    {
        return [
            'nro_legaj' => $this->nro_legaj,
            'check_retro' => $this->check_retro,
            'check_lic' => $this->check_lic,
            'check_sin_activo' => $this->check_sin_activo,
            'seguro_vida_patronal' => $this->seguro_vida_patronal,
            'TopeJubilatorioPatronal' => $this->TopeJubilatorioPatronal,
            'TopeJubilatorioPersonal' => $this->TopeJubilatorioPersonal,
            'TopeOtrosAportesPersonal' => $this->TopeOtrosAportesPersonal,
            'truncaTope' => $this->truncaTope,
            'nro_liqui' => $this->nro_liqui
        ];
    }
}
