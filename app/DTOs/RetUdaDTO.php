<?php

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class RetUdaDTO extends Data
{
    public function __construct(
        #[MapName('nro_legaj')]
        public int $nroLegaj,
        #[MapName('nro_cargo')]
        public int $nroCargo,
        public string $periodo,
        #[MapName('tipo_escal')]
        public ?string $tipoEscal = null,
        #[MapName('codc_categ')]
        public ?string $codcCateg = null,
        #[MapName('codc_agrup')]
        public ?string $codcAgrup = null,
        #[MapName('codc_carac')]
        public ?string $codcCarac = null,
        #[MapName('porc_aplic')]
        public ?float $porcAplic = null,
        #[MapName('codc_dedic')]
        public ?string $codcDedic = null,
        #[MapName('hs_cat')]
        public ?float $hsCat = null,
        public ?float $antiguedad = null,
        public ?float $permanencia = null,
        public ?float $porchaber = null,
        #[MapName('lic_50')]
        public ?string $lic50 = null,
        #[MapName('impp_basic')]
        public ?float $imppBasic = null,
        #[MapName('zona_desf')]
        public ?int $zonaDesf = null,
        public ?int $riesgo = null,
        #[MapName('falla_caja')]
        public ?int $fallaCaja = null,
        #[MapName('ded_excl')]
        public ?int $dedExcl = null,
        #[MapName('titu_nivel')]
        public ?string $tituNivel = null,
        public ?int $subrog = null,
        #[MapName('cat_108')]
        public ?string $cat108 = null,
        #[MapName('basico_108')]
        public ?float $basico108 = null,
        #[MapName('nro_liqui')]
        public ?int $nroLiqui = null,
        #[MapName('cat_basico_7')]
        public ?float $catBasico7 = null,
        #[MapName('cat_basico_v_perm')]
        public ?float $catBasicoVPerm = null,
        #[MapName('codc_uacad')]
        public ?string $codcUacad = null,
        public ?string $coddependesemp = null,
        #[MapName('adi_col_sec')]
        public ?int $adiColSec = null,
    ) {
    }
}
