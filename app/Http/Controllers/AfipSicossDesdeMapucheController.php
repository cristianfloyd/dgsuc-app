<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AfipSicossDesdeMapuche;
use App\Traits\MapucheConnectionTrait;
use App\Models\afipImportacionCrudaModel;

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

    public function create(){
        return view('afip-sicoss-desde-mapuche.create');
    }

    public function transformar (){
        //
    }

    public function store(Request $request){
        $data = $request->validate([
        // Validar los datos
        ]);
        //
    }

}
