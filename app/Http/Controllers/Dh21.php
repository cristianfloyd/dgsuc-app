<?php

namespace App\Http\Controllers;

use Exception;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;

class Dh21 extends Controller
{
    use MapucheConnectionTrait;

    public function index(): void
    {
        try {
            $connection = DB::connection($this->getConnectionName());
            $connection->getPdo();
            echo 'Conexión exitosa a la base de datos' . $this->getConnectionName();
        } catch (Exception $e) {
            echo 'Error al conectar a la base de datos' . $this->getConnectionName() . ':' . $e->getMessage();
        }
    }
}
