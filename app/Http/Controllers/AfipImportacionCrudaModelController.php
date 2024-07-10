<?php

namespace App\Http\Controllers;

use App\Models\UploadedFile;
use Illuminate\Http\Request;
use App\Models\AfipImportacionCrudaModel;
use Illuminate\Support\Facades\Validator;

class AfipImportacionCrudaModelController extends Controller
{
    // Agregar los metodos basicos de un controlador
    public function index()
    {
        $archivos = UploadedFile::all();
        return view('importar.index', compact('archivos'));
    }

    public function show(AfipImportacionCrudaModel $afip_importacion_cruda_model){
        return view('importar.show', compact('afip_importacion_cruda_model'));
    }
    public function create()
    {
        return view('importar.create');
    }


    
    public function store(Request $request)
    {
        if ($this->VerificarArchivo($request))
        {
            $lineas = $this->importar($request);
            return "Se han importado $lineas lineas";
        }
    }

    /*
    * La funcion importar se encarga de importar un archivo de texto a la base de datos
    * utilizando el modelo AfipImportacionCrudaModel y los metodos de ese modelo
    */
    public function importar(Request $request)
    {
        $ImportacionCruda = new AfipImportacionCrudaModel();
        //$lineas =  $ImportacionCruda->importar($request);
        $lineas = $ImportacionCruda->importarArchivo($request);
        return $lineas;
    }



    /*
    * maneja la importacion de un archivo txt a la base de datos
    *
    */
    public function VerificarArchivo(Request $request)
    {
        /**
         * Valida que el archivo subido sea de tipo txt.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return void
         */
        $Validated = Validator::make($request->all(), [
            'archivo' => 'required|file|mimes:txt',
        ]);
        if ($Validated->fails()) {
            /**
             * Devuelve la respuesta de la validación fallida con los errores y los datos de entrada.
             *
             * Este método se utiliza para manejar la validación fallida de un formulario o solicitud.
             * Redirige al usuario a la página anterior con los errores de validación y los datos de entrada
             * para que el usuario pueda corregir los errores y volver a enviar la solicitud.
             *
             * @return \Illuminate\Http\RedirectResponse
             */
            return back()->withErrors($Validated)->withInput();
        }
        // si todas las validaciones pasaron se retorna true
        return true;
    }
}

