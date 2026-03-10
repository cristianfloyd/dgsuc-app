<?php

namespace App\Http\Controllers;

use App\Models\AfipSicossDesdeMapuche;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AfipSicossDesdeMapucheController extends Controller
{
    use MapucheConnectionTrait;

    public function index()
    {
        $registros = DB::connection($this->getConnectionName())->table('afip_sicoss_desde_mapuche')->get();
        return view('afip-sicoss-mapuche.index', compact('registros'));
    }

    // public function show(AfipSicossDesdeMapuche $afip_sicoss_desde_mapuche){
    //     return view('afip-sicoss-desde-mapuche.show', compact('afip_sicoss_desde_mapuche'));
    // }

    public function create()
    {
        return view('afip-sicoss-desde-mapuche.create');
    }

    public function transformar(): void
    {
    }

    public function store(Request $request): void
    {
        $data = $request->validate([
            // Validar los datos
        ]);
    }
}
