<?php

namespace App\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class RetUdaDTO extends DataTransferObject
{
    public int $nroLegaj;
    public int $nroCargo;
    public string $periodo;
    public ?string $tipoEscal;
    public ?string $codcCateg;
    public ?string $codcAgrup;
    public ?string $codcCarac;
    public ?float $porcAplic;
    public ?string $codcDedic;
    public ?float $hsCat;
    public ?float $antiguedad;
    public ?float $permanencia;
    public ?float $porchaber;
    public ?string $lic50;
    public ?float $imppBasic;
    public ?int $zonaDesf;
    public ?int $riesgo;
    public ?int $fallaCaja;
    public ?int $dedExcl;
    public ?string $tituNivel;
    public ?int $subrog;
    public ?string $cat108;
    public ?float $basico108;
    public ?int $nroLiqui;
    public ?float $catBasico7;
    public ?float $catBasicoVPerm;
    public ?string $codcUacad;
    public ?string $coddependesemp;
    public ?int $adiColSec;

    public static function fromArray(array $data): self
    {
        return new self([
            'nroLegaj' => $data['nro_legaj'],
            'nroCargo' => $data['nro_cargo'],
            'periodo' => $data['periodo'],
            'tipoEscal' => $data['tipo_escal'] ?? null,
            'codcCateg' => $data['codc_categ'] ?? null,
            'codcAgrup' => $data['codc_agrup'] ?? null,
            'codcCarac' => $data['codc_carac'] ?? null,
            'porcAplic' => $data['porc_aplic'] ?? null,
            'codcDedic' => $data['codc_dedic'] ?? null,
            'hsCat' => $data['hs_cat'] ?? null,
            'antiguedad' => $data['antiguedad'] ?? null,
            'permanencia' => $data['permanencia'] ?? null,
            'porchaber' => $data['porchaber'] ?? null,
            'lic50' => $data['lic_50'] ?? null,
            'imppBasic' => $data['impp_basic'] ?? null,
            'zonaDesf' => $data['zona_desf'] ?? null,
            'riesgo' => $data['riesgo'] ?? null,
            'fallaCaja' => $data['falla_caja'] ?? null,
            'dedExcl' => $data['ded_excl'] ?? null,
            'tituNivel' => $data['titu_nivel'] ?? null,
            'subrog' => $data['subrog'] ?? null,
            'cat108' => $data['cat_108'] ?? null,
            'basico108' => $data['basico_108'] ?? null,
            'nroLiqui' => $data['nro_liqui'] ?? null,
            'catBasico7' => $data['cat_basico_7'] ?? null,
            'catBasicoVPerm' => $data['cat_basico_v_perm'] ?? null,
            'codcUacad' => $data['codc_uacad'] ?? null,
            'coddependesemp' => $data['coddependesemp'] ?? null,
            'adiColSec' => $data['adi_col_sec'] ?? null,
        ]);
    }

    public function toArray(): array
    {
        return [
            'nro_legaj' => $this->nroLegaj,
            'nro_cargo' => $this->nroCargo,
            'periodo' => $this->periodo,
            'tipo_escal' => $this->tipoEscal,
            'codc_categ' => $this->codcCateg,
            'codc_agrup' => $this->codcAgrup,
            'codc_carac' => $this->codcCarac,
            'porc_aplic' => $this->porcAplic,
            'codc_dedic' => $this->codcDedic,
            'hs_cat' => $this->hsCat,
            'antiguedad' => $this->antiguedad,
            'permanencia' => $this->permanencia,
            'porchaber' => $this->porchaber,
            'lic_50' => $this->lic50,
            'impp_basic' => $this->imppBasic,
            'zona_desf' => $this->zonaDesf,
            'riesgo' => $this->riesgo,
            'falla_caja' => $this->fallaCaja,
            'ded_excl' => $this->dedExcl,
            'titu_nivel' => $this->tituNivel,
            'subrog' => $this->subrog,
            'cat_108' => $this->cat108,
            'basico_108' => $this->basico108,
            'nro_liqui' => $this->nroLiqui,
            'cat_basico_7' => $this->catBasico7,
            'cat_basico_v_perm' => $this->catBasicoVPerm,
            'codc_uacad' => $this->codcUacad,
            'coddependesemp' => $this->coddependesemp,
            'adi_col_sec' => $this->adiColSec,
        ];
    }
}
