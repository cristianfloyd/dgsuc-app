<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail(int|null $param)
 * @method where(string $string, $id)
 * @method static find(int $archivoId)
 */
class UploadedFile extends Model
{
    protected $connection = 'pgsql-mapuche';
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
     * Create a new uploaded file record in the database.
     *
     * This method creates a new instance of UploadedFile with the given data
     * and saves it to the database.
     *
     * @param array $data An associative array containing the attributes for the new UploadedFile
     * @return UploadedFile The newly created UploadedFile instance
     */
    public static function create(array $data): UploadedFile
    {
        $uploadedFile = new static();
        $uploadedFile->fill($data);
        $uploadedFile->save();

        return $uploadedFile;
    }


	/**
	 * @return mixed
	 */
	public function get(): mixed
    {
		return $this->table;
	}
    public function getById($id) {
        return $this->where('id', $id)->first();
    }
    public function scopeSearch($query, $search)
    {
        return $query->where('filename', 'like', "%$search%")
            ->orWhere('original_name', 'like', "%$search%");
    }
}
