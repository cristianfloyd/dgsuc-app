<?php

namespace App\Http\Controllers;

use App\Models\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UploadController extends Controller
{
    //Muestra la lista de archivos cargados
    public function index()
    {
        $archivos = UploadedFile::all();
        return view('uploads.index', compact('archivos'));
    }

    //Muestra el formulario de cargado de archivos
    public function create()
    {
        return view('uploads.create');
    }

    // //Almacena el archivo cargado en el servidor
    // public function store(UploadFileRequest $request, FileUploadService $fileUploadService): RedirectResponse
    // {
    //     //dd($request->user());
    //     try {
    //         $uploadedFile = $fileUploadService->uploadFile($request->file('file_upload'));
    //     } catch (\Exception $e) {
    //         return Redirect::back()->withErrors(['error' => $e->getMessage()]);
    //     }
    //     return redirect()->route('uploads.index');
    // }

    //almacena el archivo cargado en el servidor
    public function store(Request $request): RedirectResponse
    {
        //Validar el archivo que se recibe. Rechazar si no es de tipo txt y si el tamaño es mayor a 20MB
        $request->validate([
            'file_upload' => 'required|file|max:20000',
        ]);
        $file = $request->file('file_upload');
        $filename = $file->getClientOriginalName();
        //Generar un nombre unico para el archivo
        $extension = $request->file('file_upload')->getClientOriginalExtension();
        $time = time();
        $filename = $filename . '_' . $time . '.' . $extension;

        //Intentar almacenar el archivo en la carpeta storage/app/public/uploads
        //Si falla, rechazar la solicitud y mostrar un mensaje de error
        try {
            $filepath = $file->storeAs('uploads', $filename, 'public');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => $e->getMessage()]);
        }

        //Guardar la informacion en la base de datos con el modelo UploadedFile.
        //El modelo UploadedFile tiene un campo filename que es el nombre del archivo con el que se guardo en el servidor
        //El modelo UploadedFile tiene un campo ogiginal_name que es el nombre del archivo original
        //El modelo UploadedFile tiene un campo filepath que es el path del archivo en el servidor
        $archivoCargado = new UploadedFile();
        $archivoCargado->filename = $filename;
        $archivoCargado->original_name = $file->getClientOriginalName();
        $archivoCargado->file_path = $filepath;
        $archivoCargado->user_id = auth()->user()->id;
        $archivoCargado->user_name = auth()->user()->name;
        $archivoCargado->save();

        //Redirigir a la pagina de inicio con un mensaje de exito
        return redirect()->route('uploads.index')
            ->with('status', "Archivo '{$archivoCargado->original_name}' cargado correctamente.");
    }
}
