<?php

namespace App\Contracts;

use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Collection;

interface FileUploadRepositoryInterface
{
    /**
     * Encuentra o lanza una excepción si no se encuentra un modelo por su ID.
     *
     * @param int $id El ID del modelo a buscar.
     * @return \App\Models\UploadedFile El modelo encontrado.
     */
    public function findOrFail($id): UploadedFile;

    /**
     * Elimina un modelo.
     *
     * @param \App\Models\UploadedFile $model El modelo a eliminar.
     * @return bool Verdadero si el modelo fue eliminado correctamente.
     */
    public function delete($model): bool;

    /**
     * Crea un nuevo registro de archivo cargado.
     *
     * @param array $data Los datos del nuevo archivo cargado.
     * @return \App\Models\UploadedFile El nuevo archivo cargado creado.
     */
    public function create(array $data): UploadedFile;

    /**
     * Obtiene todos los archivos cargados.
     *
     * @return \App\Models\UploadedFile[] Todos los archivos cargados.
     */
    public function all(): Collection|array;

    /**
     * Verifica si existe un archivo cargado con el origen especificado.
     *
     * @param string $origen El origen a buscar.
     * @return bool Verdadero si existe un archivo cargado con el origen especificado, falso en caso contrario.
     */
    public function existsByOrigen(string $origen): bool;


    /**
     * Obtiene el último archivo cargado con el origen especificado.
     *
     * @param string $origen El origen a buscar.
     * @return \App\Models\UploadedFile|null El último archivo cargado con el origen especificado, o null si no se encuentra.
     */
    public function getLatestByOrigen(string $origen): ?UploadedFile;
}
