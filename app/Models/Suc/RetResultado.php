<?php

namespace App\Models\Suc;

use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetResultado extends Model
{
    use HasFactory, MapucheConnectionTrait;



    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'suc.ret_resultado';

    /**
     * La clave primaria compuesta del modelo.
     *
     * @var array
     */
    protected $primaryKey = ['nro_legaj', 'nro_cargo_ant', 'fecha_ret_desde', 'periodo'];

    public $incrementing = false;
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nro_legaj', 'nro_cargo_nuevo', 'categ_n', 'agrup_n', 'dedid_n',
        'cat_basico_n', 'nro_cargo_ant', 'categ_v', 'agrup_v', 'dedid_v',
        'cat_basico_v', 'anios_n', 'anios_v', 'titulo_n', 'titulo_v',
        // ... (incluir todos los campos de la tabla)
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'cat_basico_n' => 'float',
        'cat_basico_v' => 'float',
        'anios_n' => 'integer',
        'anios_v' => 'integer',
        'x11_n' => 'boolean',
        'x11_v' => 'boolean',
        'zona_n' => 'boolean',
        'zona_v' => 'boolean',
        // ... (incluir todos los campos booleanos y numéricos)
        'fecha_ret_desde' => 'date',
        'fecha_ret_hasta' => 'date',
    ];

    /**
     * Obtiene el resultado del retroactivo para un legajo específico.
     *
     * @param int $nroLegaj
     * @return RetResultado|null
     */
    public static function obtenerPorLegajo(int $nroLegaj): ?RetResultado
    {
        return self::where('nro_legaj', $nroLegaj)->first();
    }

    /**
     * Obtiene el periodo como un ValueObject Periodo.
     *
     * @return Periodo
     */
    public function getPeriodoAttribute(): Periodo
    {
        return new Periodo($this->attributes['periodo']);
    }

    /**
     * Establece el periodo desde un ValueObject Periodo.
     *
     * @param Periodo $periodo
     */
    public function setPeriodoAttribute(Periodo $periodo): void
    {
        $this->attributes['periodo'] = $periodo->getValue();
    }

    /**
     * Obtiene el tipo_retro como un ValueObject TipoRetro.
     *
     * @return TipoRetro
     */
    public function getTipoRetroAttribute(): TipoRetro
    {
        return new TipoRetro($this->attributes['tipo_retro']);
    }

    /**
     * Establece el tipo_retro desde un ValueObject TipoRetro.
     *
     * @param TipoRetro $tipoRetro
     */
    public function setTipoRetroAttribute(TipoRetro $tipoRetro): void
    {
        $this->attributes['tipo_retro'] = $tipoRetro->getValue();
    }
}
