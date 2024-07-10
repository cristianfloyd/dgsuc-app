<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class dh21 extends Controller
{
    public function index(){
        try {
            $connection = DB::connection('pgsql-mapuche');
            $connection->getPdo();
            echo "Conexión exitosa a la base de datos pgsql-mapuche";
        } catch (\Exception $e) {
            echo "Error al conectar a la base de datos pgsql-mapuche: " . $e->getMessage();
        }
    }
}
