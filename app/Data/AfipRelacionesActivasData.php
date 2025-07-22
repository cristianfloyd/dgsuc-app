<?php

namespace App\Data;

use App\Models\AfipRelacionesActivas;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class AfipRelacionesActivasData extends Data
{
    public function __construct(
        public readonly string $periodoFiscal,
        public readonly string $codigoMovimiento,
        public readonly string $tipoRegistro,
        public readonly string $cuil,
        public readonly string $marcaTrabajadorAgropecuario,
        public readonly string $modalidadContrato,
        public readonly Carbon $fechaInicioRelacionLaboral,
        public readonly ?Carbon $fechaFinRelacionLaboral,
        public readonly string $codigoOSocial,
        public readonly string $codSituacionBaja,
        public readonly ?Carbon $fechaTelegramaRenuncia,
        public readonly float $retribucionPactada,
        public readonly string $modalidadLiquidacion,
        public readonly string $sucDomicilioDesem,
        public readonly string $actividadDomicilioDesem,
        public readonly string $puestoDesem,
        public readonly string $rectificacion,
        public readonly string $numeroFormularioAgro,
        public readonly string $tipoServicio,
        public readonly string $categoriaProfesional,
        public readonly string $ccct,
        public readonly string $noHayDatos,
    ) {
    }

    public static function fromModel(AfipRelacionesActivas $model): self
    {
        return new self(
            $model->periodo_fiscal,
            $model->codigo_movimiento,
            $model->tipo_registro,
            $model->cuil,
            $model->marca_trabajador_agropecuario,
            $model->modalidad_contrato,
            $model->fecha_inicio_relacion_laboral,
            $model->fecha_fin_relacion_laboral,
            $model->codigo_o_social,
            $model->cod_situacion_baja,
            $model->fecha_telegrama_renuncia,
            $model->retribucion_pactada,
            $model->modalidad_liquidacion,
            $model->suc_domicilio_desem,
            $model->actividad_domicilio_desem,
            $model->puesto_desem,
            $model->rectificacion,
            $model->numero_formulario_agro,
            $model->tipo_servicio,
            $model->categoria_profesional,
            $model->ccct,
            $model->no_hay_datos,
        );
    }
}
