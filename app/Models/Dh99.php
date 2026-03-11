<?php

namespace App\Models;

use App\Services\Mapuche\PeriodoFiscalService;
use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\PeriodoFiscal;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Override;

use function is_string;

// (D) Variable Global: Período Corriente

class Dh99 extends Model
{
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indica si la clave primaria es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh99';

    /**
     * La clave primaria compuesta asociada con la tabla.
     *
     * @var array
     */
    protected $primaryKey = ['per_anoct', 'per_mesct'];

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'per_anoct',  // Año del período corriente
        'per_mesct',  // Mes del período corriente
        'codc_uacad',
    ];

    /**
     * Establece el periodo fiscal a partir de un objeto PeriodoFiscal o un string en formato YYYYMM.
     *
     * @param PeriodoFiscal|string $periodoFiscal
     */
    public function setPeriodoFiscal($periodoFiscal): bool
    {
        try {
            if (is_string($periodoFiscal)) {
                $periodoFiscal = PeriodoFiscal::fromString($periodoFiscal);
            }

            if (!$periodoFiscal instanceof PeriodoFiscal) { // @phpstan-ignore instanceof.alwaysTrue
                throw new InvalidArgumentException('El periodo fiscal debe ser un string en formato YYYYMM o un objeto PeriodoFiscal');
            }

            $this->per_anoct = $periodoFiscal->year();
            $this->per_mesct = $periodoFiscal->month();

            return $this->save();
        } catch (Exception $e) {
            Log::error('Error al establecer el periodo fiscal: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Obtiene el periodo fiscal formateado como YYYYMM.
     */
    protected function periodoFiscal(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $periodoFiscalService = resolve(PeriodoFiscalService::class);
                $periodo = $periodoFiscalService->getPeriodoFiscalFromDatabase();

                return $periodo['year'] . $periodo['month'];
            },
        );
    }

    /**
     * Obtiene el periodo fiscal como un objeto PeriodoFiscal.
     */
    protected function periodoFiscalObject(): Attribute
    {
        return Attribute::make(
            get: fn(): \App\ValueObjects\PeriodoFiscal => new PeriodoFiscal($this->per_anoct, $this->per_mesct),
        );
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'per_anoct' => 'integer',
            'per_mesct' => 'integer',
            'codc_uacad' => 'string',
        ];
    }
}
