<?php

namespace App\Repositories;

use App\Contracts\FileUploadRepositoryInterface;
use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FileUploadRepository implements FileUploadRepositoryInterface
{
    /**
     * Obtiene un archivo cargado por su ID o lanza una excepción si no se encuentra.
     *
     * @param  int  $id  El ID del archivo cargado a buscar.
     * @return UploadedFile El modelo del archivo cargado.
     *
     * @throws ModelNotFoundException Si no se encuentra el archivo cargado.
     */
    public function findOrFail($id): UploadedFile
    {
        return UploadedFile::query()->findOrFail($id);
    }

    /**
     * Crea un nuevo registro de archivo cargado.
     */
    public function create(array $data): UploadedFile
    {
        return UploadedFile::query()->create($data);
    }

    /**
     * Elimina un archivo cargado.
     *
     * @param  UploadedFile  $model  El modelo de archivo cargado a eliminar.
     * @return bool Verdadero si el archivo se eliminó correctamente, falso en caso contrario.
     */
    public function delete($model): bool
    {
        return $model->delete();
    }

    /**
     * Obtiene todos los archivos cargados.
     *
     * @return Collection|UploadedFile[]
     */
    public function all(): Collection|array
    {
        return UploadedFile::all();
    }

    /**
     * Verifica si existe un archivo cargado con el origen especificado.
     *
     * @param  string  $origen  El origen del archivo cargado a buscar.
     * @return bool Verdadero si existe un archivo cargado con el origen especificado, falso en caso contrario.
     */
    public function existsByOrigen(string $origen): bool
    {
        return UploadedFile::query()->where('origen', $origen)->exists();
    }

    /**
     * Get the latest uploaded file by origen.
     */
    public function getLatestByOrigen(string $origen): ?UploadedFile
    {
        return UploadedFile::query()->where('origen', $origen)
            ->latest()
            ->first();
    }
}
