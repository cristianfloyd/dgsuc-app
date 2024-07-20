<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TablaTempCuils extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.tabla_temp_cuils';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cuil',
    ];
    public $timestamps = false;

    public static function tableExists()
    {
        $instance = new TablaTempCuils();
        $connection = $instance->getConnectionName();
        $table = $instance->getTable();
        return schema::connection($connection)->hasTable($table);
    }

    public function createTable()
    {
        Schema::connection($this->connection)->create($this->table, function ($table) {
            $table->id();
            $table->string('cuil', 11)->unique();
        });
    }

    public static function dropTable(): void
    {
        $connection = (new TablaTempCuils())->getConnectionName();
        $table = (new TablaTempCuils())->getTable();
        Schema::connection($connection)->dropIfExists($table);
    }

    public static function insertTable(array $cuils): bool
    {
        try {
            DB::transaction(function () use ($cuils) {
                $data = [];
                foreach ($cuils as $cuil) {
                    $data[] = ['cuil' => $cuil];
                }

                $instance = new TablaTempCuils();
                $connection = $instance->getConnectionName();
                $table = $instance->getTable();

                DB::connection($connection)->table($table)->insert($data);
            });

            return true;
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return false;
        }
    }

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
}
