<?php

namespace App\DTOs;

class AfipMapucheSicossDTO
{
    public function __construct(
        public string $periodoFiscal,
        public string $cuil,
        public ?string $apnom = null,
        public ?string $conyuge = null,
        public ?string $cantHijos = null,
        public ?string $codSituacion = null,
        public ?string $codCond = null,
        public ?string $codAct = null,
        public ?string $codZona = null,
        public ?string $porcAporte = null,
        public ?string $codModCont = null,
        public ?string $codOs = null,
        public ?string $cantAdh = null,
        public ?string $remTotal = null,
        public ?string $remImpo1 = null,
        public ?string $asigFamPag = null,
        public ?string $aporteVol = null,
        public ?string $impAdicOs = null,
        public ?string $excAportSs = null,
        public ?string $excAportOs = null,
        public ?string $prov = null,
        public ?string $remImpo2 = null,
        public ?string $remImpo3 = null,
        public ?string $remImpo4 = null,
        public ?string $codSiniestrado = null,
        public ?string $marcaReduccion = null,
        public ?string $recompLrt = null,
        public ?string $tipoEmpresa = null,
        public ?string $aporteAdicOs = null,
        public ?string $regimen = null,
        public ?string $sitRev1 = null,
        public ?string $diaIniSitRev1 = null,
        public ?string $sitRev2 = null,
        public ?string $diaIniSitRev2 = null,
        public ?string $sitRev3 = null,
        public ?string $diaIniSitRev3 = null,
        public ?string $sueldoAdicc = null,
        public ?string $sac = null,
        public ?string $horasExtras = null,
        public ?string $zonaDesfav = null,
        public ?string $vacaciones = null,
        public ?string $cantDiasTrab = null,
        public ?string $remImpo5 = null,
        public ?string $convencionado = null,
        public ?string $remImpo6 = null,
        public ?string $tipoOper = null,
        public ?string $adicionales = null,
        public ?string $premios = null,
        public ?string $remDec78805 = null,
        public ?string $remImp7 = null,
        public ?string $nroHorasExt = null,
        public ?string $cptoNoRemun = null,
        public ?string $maternidad = null,
        public ?string $rectificacionRemun = null,
        public ?string $remImp9 = null,
        public ?string $contribDif = null,
        public ?string $hstrab = null,
        public ?string $seguro = null,
        public ?string $ley27430 = null,
        public ?string $incsalarial = null,
        public ?string $remimp11 = null,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
