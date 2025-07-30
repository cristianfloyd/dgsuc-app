<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TablaTempCuils extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'afip_tabla_temp_cuils';
    protected $primaryKey = 'id';
    protected $schema = 'suc';

    protected $fillable = [
        'cuil',
    ];
    public $timestamps = false;

    protected static function getConnectionNombre(): string
    {
        $instance = new self();
        return $instance->getConnectionName();
    }

    /** Verifica si la tabla suc.tabla_temp_cuils existe en la base de datos.
    *
    * Devuelve verdadero si la tabla existe, falso en caso contrario.
     * @return bool
     * */
    public static function tableExists()
    {
        $instance = new TablaTempCuils();
        $connection = $instance->getConnectionName();
        $table = $instance->getTable();
        return schema::connection($connection)->hasTable($table);
    }

    /** Crea la tabla suc.tabla_temp_cuils con una columna id como clave primaria
     * y una columna cuil de tipo string de 11 caracteres que es única.
     * @return void
     *
     */
    public function createTable(): void
    {
        Schema::connection($this->getConnectionName())->create($this->table, function ($table) {
            $table->id();
            $table->string('cuil', 11)->unique();
        });
    }


    /** Elimina la tabla suc.tabla_temp_cuils si existe.
     */
    public static function dropTable(): void
    {
        $connection = (new TablaTempCuils())->getConnectionName();
        $table = (new TablaTempCuils())->getTable();
        Schema::connection($connection)->dropIfExists($table);
    }

    /** Inserta una lista de CUILs en la tabla suc.tabla_temp_cuils.
     *
     * @param array $cuils Lista de CUILs a insertar.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public static function insertTable(array $cuils): bool
    {
        try {
            DB::connection(self::getConnectionNombre())->transaction(function () use ($cuils) {
                $data = [];
                foreach ($cuils as $cuil) {
                    $data[] = ['cuil' => $cuil];
                    $instance = new TablaTempCuils();
                    $connection = $instance->getConnectionName();
                }

                $table = $instance->getTable();

                DB::connection($connection)->table($table)->insert($data);
            });

            return true;
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return false;
        }
    }

    /** Inserta datos en la tabla suc.afip_mapuche_mi_simplificacion utilizando la función suc.get_mi_simplificacion_tt.
     *
     * @param int $nroLiqui Número de liquidación.
     * @param int $periodoFiscal Período fiscal.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public static function mapucheMiSimplificacion($nroLiqui, $periodoFiscal): bool
    {
        try {
            // Obtener la conexión a la base de datos
            $connection = (new self())->getConnectionName();

            // Ejecutar la consulta de inserción
            DB::connection($connection)->statement(
                'INSERT INTO suc.afip_mapuche_mi_simplificacion
                SELECT * FROM suc.get_mi_simplificacion_tt(?, ?)',
                [$nroLiqui, $periodoFiscal]
            );
            Log::info('insert into suc.afip_mapuche_mi_simplificacion exitoso. Desde tablaTemCuils model');
            // Devolver un valor que indique éxito
            return true;
        } catch (\Exception $e) {
            // Manejo del error: puedes registrar el error si es necesario
            Log::error($e->getMessage());
            return 0;
        }
    }

    public function scopeCuil($query, $cuil)
    {
        return $query->where('cuil', $cuil);
    }

    public function getSchemaName(): string
    {
        return $this->schema;
    }

    public function getFullTableName(): string
    {
        return "{$this->schema}.{$this->table}";
    }
}
