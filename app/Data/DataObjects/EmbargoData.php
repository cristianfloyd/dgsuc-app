<?php
declare(strict_types=1);

namespace App\Data\DataObjects;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class EmbargoData extends Data
{
    public function __construct(
        public readonly int $nro_legaj,
        public readonly int $id_tipo_remuneracion,
        public readonly int $id_tipo_embargo,
        public readonly int $id_estado_embargo,
        public readonly int $id_juzgado,
        public readonly string $cuit,
        public readonly string $nro_expediente_institucion,
        public readonly int $codn_conce,
        public readonly ?int $es_de_legajo = null,
        public readonly ?int $nro_oficio = null,
        public readonly ?string $lugar_pago = null,
        public readonly ?string $nro_expediente_original = null,
        public readonly ?string $nro_expediente_ampliatorio = null,
        public readonly ?string $caratula = null,
        public readonly ?Carbon $fec_inicio = null,
        public readonly ?Carbon $fec_finalizacion = null,
        public readonly ?float $imp_embargo = null,
        public readonly ?Carbon $fec_ingreso_expediente = null,
        public readonly ?string $obs_embargo = null,
        public readonly ?int $prioridad = null,
        public readonly ?string $nro_cuenta_judicial = null,
        public readonly ?int $codigo_sucursal = null,
        public readonly ?int $nroentidadbancaria = null,
        public readonly ?float $cuota_embargo = null,
        public readonly ?int $id_tipo_juicio = null,
        public readonly ?string $nom_demandado = null,
        public readonly ?Carbon $fec_oficio = null,
        public readonly ?int $id_tipo_expediente = null,
    ) {}
}
