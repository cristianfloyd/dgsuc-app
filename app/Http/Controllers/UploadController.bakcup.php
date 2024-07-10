<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\afipImportacionCrudaModel;

class UploadController extends Controller
{
    public function index (){
        return view('importar.upload-sicoss');
    }


    public function upload(Request $request)
    {
        $request->validate([ 'file' => 'required|file|mimes:txt', ]);

        //Almacenar el archivo subido
        $path = $request->file('file')->store('uploads');

        //Leer el archivo y procesar las lineas
        $file = Storage::get($path);
        $lines = explode(PHP_EOL, $file);
        $importacion = new afipImportacionCrudaModel();
        $importacion->truncateTableIfNotEmpty();
        // Insertar las lineas en la base de datos
        foreach ($lines as $line){
            if(!empty($line)){
                DB::connection('pgsql_mapuche')
                    ->table('afip_importacion_cruda')
                    ->insert(['linea_completa' => $line]);
            } else {
                return back()->with('error', 'El archivo no puede estar vacío');
            }
        }
        return back()->with('success', 'Archivo subido correctamente');

    }
}
