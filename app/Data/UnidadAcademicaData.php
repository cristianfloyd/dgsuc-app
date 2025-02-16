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
            'CBX' => new self('CBX', '4', TipoActividad::UNIVERSITARIA),
            'CXX' => new self('CXX', '7', TipoActividad::TERCIARIA),
            'VTX' => new self('VTX', '21', TipoActividad::UNIVERSITARIA),
            'SOX' => new self('SOX', '20', TipoActividad::TERCIARIA),
            'SIX' => new self('SIX', '19', TipoActividad::TERCIARIA),
            'RFX' => new self('RFX', '18', TipoActividad::INICIAL),
            'ODX' => new self('ODX', '15', TipoActividad::UNIVERSITARIA),
            'MDX' => new self('MDX', '14', TipoActividad::TERCIARIA),
            'IVX' => new self('IVX', '13', TipoActividad::INTERNACION),
            'HCX' => new self('HCX', '11', TipoActividad::INTERNACION),
            'FLX' => new self('FLX', '10', TipoActividad::TERCIARIA),
            'FCX' => new self('FCX', '9', TipoActividad::UNIVERSITARIA),
            'DRX' => new self('DRX', '8', TipoActividad::TERCIARIA),
            'CEX' => new self('CEX', '5', TipoActividad::UNIVERSITARIA),
            'AGX' => new self('AGX', '1', TipoActividad::TERCIARIA),
            'BAX' => new self('BAX', '3', TipoActividad::SECUNDARIA),
            'IGX' => new self('IGX', '12', TipoActividad::TERCIARIA),
            'CPX' => new self('CPX', '6', TipoActividad::PRIMARIA),
            'RCX' => new self('RCX', '17', TipoActividad::ADMINISTRATIVA),
            'OSX' => new self('OSX', '16', TipoActividad::ADMINISTRATIVA),
            default => null,
        };
    }
}
