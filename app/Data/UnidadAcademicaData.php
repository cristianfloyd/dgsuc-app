<?php

namespace App\Data;

use App\Enums\TipoActividad;
use Spatie\LaravelData\Data;
use Illuminate\Support\Facades\Log;

class UnidadAcademicaData extends Data
{
    public function __construct(
        public readonly string $codigo,
        public readonly string $sucursal,
        public readonly TipoActividad $actividad,
    ) {}

    public static function fromCodigo(string $codigo): ?self
    {
        if(!$codigo) return null;

        $codigo = trim(strtoupper($codigo));

        return match ($codigo) {
            'AGX' => new self('AGX', '00001', TipoActividad::TERCIARIA),
            'AQX' => new self('AQX', '00007', TipoActividad::TERCIARIA),  // AQX y CXX tienen el mismo código de unidad académica
            'BAX' => new self('BAX', '00003', TipoActividad::SECUNDARIA),
            'CBX' => new self('CBX', '00004', TipoActividad::UNIVERSITARIA),
            'CEX' => new self('CEX', '00005', TipoActividad::UNIVERSITARIA),
            'CPX' => new self('CPX', '00006', TipoActividad::PRIMARIA),
            'CXX' => new self('CXX', '00007', TipoActividad::TERCIARIA),
            'DRX' => new self('DRX', '00008', TipoActividad::TERCIARIA),
            'FCX' => new self('FCX', '00009', TipoActividad::UNIVERSITARIA),
            'FLX' => new self('FLX', '00010', TipoActividad::TERCIARIA),
            'HCX' => new self('HCX', '00011', TipoActividad::INTERNACION),
            'IGX' => new self('IGX', '00012', TipoActividad::TERCIARIA),
            'IVX' => new self('IVX', '00013', TipoActividad::INTERNACION),
            'MDX' => new self('MDX', '00014', TipoActividad::TERCIARIA),
            'ODX' => new self('ODX', '00015', TipoActividad::UNIVERSITARIA),
            'OSX' => new self('OSX', '00016', TipoActividad::ADMINISTRATIVA),
            'RCX' => new self('RCX', '00017', TipoActividad::ADMINISTRATIVA),
            'RFX' => new self('RFX', '00018', TipoActividad::INICIAL),
            'SIX' => new self('SIX', '00019', TipoActividad::TERCIARIA),
            'SOX' => new self('SOX', '00020', TipoActividad::TERCIARIA),
            'VTX' => new self('VTX', '00021', TipoActividad::UNIVERSITARIA),
            'ELX' => new self('ELX', '00022', TipoActividad::SECUNDARIAL),
            default => null,
        };
    }
}
