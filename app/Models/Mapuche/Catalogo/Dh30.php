<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Dh03;
use App\Models\Mapuche\Dh19;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Representa un modelo de la tabla 'mapuche.dh30' en la base de datos.
 * Esta tabla contiene información sobre los ítems de una tabla específica.
 * Tabla multiple de abreviaturas.
 *
 * @property int $nro_tabla
 * @property string $desc_abrev
 * @property string $desc_item
 *
 * @method HasMany dh08()
 *     Obtiene una colección de modelos Dh08 relacionados con este modelo.
 *
 * @method HasMany dh03()
 *     Obtiene una colección de modelos Dh03 relacionados con este modelo.
 */
class Dh30 extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'dh30';
    protected $primaryKey = ['nro_tabla', 'desc_abrev'];
    protected $fillable = [
        'nro_tabla',
        'desc_abrev',
        'desc_item',
    ];

    protected $casts = [
        'nro_tabla' => 'integer',
        'desc_abrev' => 'string',
        'desc_item' => 'string',
    ];

    public static function boot()
    {
        parent::boot();

        // Establecer codificación SQL_ASCII para la conexión
        DB::statement("SET client_encoding TO 'SQL_ASCII'");

        static::retrieved(function ($model) {
            if (isset($model->desc_abrev)) {
                $model->desc_abrev = $model->handleMixedEncoding($model->desc_abrev);
            }
            if (isset($model->desc_item)) {
                $model->desc_item = $model->handleMixedEncoding($model->desc_item);
            }
        });



        static::saving(function ($model) {
            if (isset($model->attributes['desc_abrev'])) {
                $model->attributes['desc_abrev'] = EncodingService::toLatin1($model->attributes['desc_abrev']);
            }
            if (isset($model->attributes['desc_item'])) {
                $model->attributes['desc_item'] = EncodingService::toLatin1($model->attributes['desc_item']);
            }
        });
    }

    public function handleMixedEncoding($value)
    {
        if (mb_detect_encoding($value) === 'ASCII') {
            return EncodingService::toUtf8($value);
        }
        return $value;
    }



    public function scopeWithoutEncoding($query)
    {
        return $query->whereRaw("encode(desc_abrev::bytea, 'escape') IS NOT NULL");
    }

    public function scopeByEncoding($query, $encoding)
    {
        return $query->whereRaw("encode(desc_item::bytea, 'escape') IS NOT NULL")
            ->get()
            ->filter(fn($item) => mb_detect_encoding($item->desc_item) === $encoding);
    }

    public function getKeyName(): array
    {
        return ['nro_tabla', 'desc_abrev'];
    }

    public function getIncrementing(): false
    {
        return false;
    }

    protected function descAbrev(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                $value = $this->handleMixedEncoding($value);
                return trim($value);
            },
            set: function ($value) {
                if (!$value) {
                    return null;
                }
                $value = EncodingService::toLatin1($value);
                return trim($value);
            }
        );
    }

    protected function descItem(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                $value = $this->handleMixedEncoding($value);
                return trim($value);
            },
            set: function ($value) {
                if (!$value) {
                    return null;
                }
                $value = EncodingService::toLatin1($value);
                return trim($value);
            }
        );
    }

    public function dh08(): HasMany
    {
        // return $this->hasMany(Dh08::class, ['nro_tabla', 'desc_abrev'], ['nro_tabla', 'desc_abrev']);
        return $this->hasMany(Dh08::class, 'nro_tabla', 'nro_tabla')
            ->where('desc_abrev', $this->desc_abrev);
    }

    public function dh03(): HasMany
    {
        return $this->hasMany(Dh03::class, 'codc_uacad', 'desc_abrev');
    }

    public function dhe2(): HasMany
    {
        return $this->hasMany(related: Dhe2::class, foreignKey: 'nro_tabla', localKey: 'nro_tabla')
            ->where(column: 'desc_abrev', operator: $this->desc_abrev);
    }

    public function dh19(): BelongsToMany
    {
        return $this->belongsToMany(Dh19::class, 'dh30_dh19', 'nro_tabla', 'codc_uacad', 'nro_tabla', 'codc_uacad');
    }

    public function dhe4(): BelongsToMany
    {
        return $this->belongsToMany(related: Dhe4::class, table: 'dhe2', foreignPivotKey: 'nro_tabla', relatedPivotKey: 'cod_organismo', parentKey: 'nro_tabla', relatedKey: 'cod_organismo');
    }

    public function organismos(): BelongsToMany
    {
        return $this->belongsToMany(related: Dhe4::class, table: 'dhe2', foreignPivotKey: 'nro_tabla', relatedPivotKey: 'cod_organismo')
            ->withPivot('desc_abrev');
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nro_tabla', $this->getAttribute('nro_tabla'))
            ->where('desc_abrev', $this->getAttribute('desc_abrev'));
    }
}
