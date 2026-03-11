<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail(int|null $param) Encuentra un registro por ID o lanza excepción si no existe
 * @method where(string $string, $id) Método para construir consultas con condiciones where
 * @method static find(int $archivoId) Encuentra un registro por ID o devuelve null si no existe
 */
class UploadedFile extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.uploaded_files';

    protected $fillable = [
        'periodo_fiscal',
        'nro_liqui',
        'origen',
        'filename',
        'original_name',
        'file_path',
        'user_id',
        'user_name',
        'process_id',  // Nuevo campo para el ID del proceso
    ];

    /**
     * Obtiene el nombre de la tabla asociada al modelo.
     *
     * @return string El nombre de la tabla.
     */
    public function get(): mixed
    {
        return $this->table;
    }

    /**
     * Obtiene un registro por su ID.
     *
     * @param int $id El ID del registro a buscar.
     *
     * @return UploadedFile|null El registro encontrado o null si no existe.
     */
    public function getById($id)
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Busca registros en la tabla de archivos cargados.
     *
     * @param Builder $query El objeto de consulta.
     * @param string $search El término de búsqueda.
     *
     * @return Builder El objeto de consulta con las condiciones de búsqueda aplicadas.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function search($query, $search)
    {
        return $query
            ->where('filename', 'like', "%$search%")
            ->orWhere('original_name', 'like', "%$search%");
    }
}
