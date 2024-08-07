<?php

namespace App\Repositories;

use App\Models\UploadedFile;
use App\Contracts\FileUploadRepositoryInterface;

class UploadedFileRepository implements FileUploadRepositoryInterface
{
    /**
     * Obtiene un archivo cargado por su ID o lanza una excepción si no se encuentra.
     *
     * @param int $id El ID del archivo cargado a buscar.
     * @return UploadedFile El modelo del archivo cargado.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si no se encuentra el archivo cargado.
     */
    public function findOrFail($id): UploadedFile
    {
        return UploadedFile::findOrFail($id);
    }



    /**
     * Elimina un archivo cargado.
     *
     * @param UploadedFile $model El modelo de archivo cargado a eliminar.
     * @return bool Verdadero si el archivo se eliminó correctamente, falso en caso contrario.
     */
    public function delete($model): bool
    {
        return $model->delete();
    }

    /**
     * Obtiene todos los archivos cargados.
     *
     * @return \Illuminate\Database\Eloquent\Collection|UploadedFile[]
     */
    public function all()
    {
        return UploadedFile::all();
    }

}
