<?php

namespace App\Models;

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

    public function tableExists()
    {
        return Schema::connection($this->connection)->hasTable($this->table);
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
}
