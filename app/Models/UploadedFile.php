<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
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
        'origen',
        'filename',
        'original_name',
        'file_path',
        'user_id',
        'user_name',
        'process_id', // Nuevo campo para el ID del proceso
    ];


    /**
     * Crea un nuevo registro de archivo cargado en la base de datos.
     * 
     * Este método crea una nueva instancia de UploadedFile con los datos proporcionados
     * y la guarda en la base de datos.
     *
     * @param array $data Un array asociativo que contiene los atributos para el nuevo UploadedFile
     * @return UploadedFile La instancia recién creada de UploadedFile
     */
    public static function create(array $data): UploadedFile
    {
        $uploadedFile = new static();
        $uploadedFile->fill($data);
        $uploadedFile->save();

        return $uploadedFile;
    }


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
     * @return UploadedFile|null El registro encontrado o null si no existe.
     */
    public function getById($id) {
        return $this->where('id', $id)->first();
    }

    /**
     * Busca registros en la tabla de archivos cargados.
     *
     * @param Builder $query El objeto de consulta.
     * @param string $search El término de búsqueda.
     * @return Builder El objeto de consulta con las condiciones de búsqueda aplicadas.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('filename', 'like', "%$search%")
            ->orWhere('original_name', 'like', "%$search%");
    }
}
