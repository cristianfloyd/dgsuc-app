<?php

namespace App\Repositories;

use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\FileUploadRepositoryInterface;

class FileUploadRepository implements FileUploadRepositoryInterface
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
        $uploadedFile = UploadedFile::query()->findOrFail($id);
        return $uploadedFile;
    }

    /**
     * Crea un nuevo registro de archivo cargado.
     *
     * @param array $data
     * @return \App\Models\Mapuche\UploadedFile
     */
    public function create(array $data): UploadedFile
    {
        return UploadedFile::create($data);
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
    public function all(): Collection|array
    {
        return UploadedFile::all();
    }

    /**
     * Verifica si existe un archivo cargado con el origen especificado.
     *
     * @param string $origen El origen del archivo cargado a buscar.
     * @return bool Verdadero si existe un archivo cargado con el origen especificado, falso en caso contrario.
     */
    public function existsByOrigen(string $origen): bool
    {
        return UploadedFile::where('origen', $origen)->exists();
    }

    /**
     * Get the latest uploaded file by origen.
     *
     * @param string $origen
     * @return UploadedFile|null
     */
    public function getLatestByOrigen(string $origen): ?UploadedFile
    {
        return UploadedFile::where('origen', $origen)
            ->latest()
            ->first();
    }
}
