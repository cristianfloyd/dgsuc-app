<?php

namespace App\Http\Controllers;

use App\Models\dh21;

class Dh21Controller extends Controller
{
    public function index()
    {

        // Puedes pasar los resultados a una vista
        return view('dh21', [
            'dh21s' => dh21::search('2510')
                ->orderBy('nro_legaj')
                ->paginate(10),
        ]);
    }
}
