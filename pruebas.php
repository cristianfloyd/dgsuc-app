<?php

use Illuminate\Support\Facades\DB;
// Primero instanciamos el repositorio
$repo = app(App\Repositories\Dhr3Repository::class);

// Probemos buscar un registro con datos reales
$registro1 = $repo->find(1,	160317,	112831,'H',	1);
$registro2 = $repo->findByPrimaryKey(1,	160317,	112831,'H',	1);

// Veamos el resultado
dump($registro);

// También podemos probar con datos que no existan
$noExiste = $repo->find(999999, 999999, 999, 'X', 999);
dump($noExiste); // Debería retornar null

// Podemos ver la consulta SQL generada
DB::enableQueryLog();
$registro = $repo->findByPrimaryKey(1,	160317,	112831,'H',	1);
DB::getQueryLog();
