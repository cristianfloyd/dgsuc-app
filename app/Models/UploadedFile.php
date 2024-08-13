<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Contracts\FileUploadRepositoryInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'process_id', // Nuevo  campo para el ID del proceso
    ];


    /**
     * Create a new uploaded file record in the database.
     *
     * This method creates a new instance of UploadedFile with the given data
     * and saves it to the database.
     *
     * @param array $data An associative array containing the attributes for the new UploadedFile
     * @return \App\Models\UploadedFile The newly created UploadedFile instance
     */
    public static function create(array $data)
    {
        $uploadedFile = new static();
        $uploadedFile->fill($data);
        $uploadedFile->save();

        return $uploadedFile;
    }


	/**
	 * @return mixed
	 */
	public function get() {
		return $this->table;
	}
    public function getById($id) {
        return $this->where('id', $id)->first();
    }
    public function scopeSearch($query, $search)
    {
        return $query->where('filename', 'like', '%' . $search . '%')
            ->orWhere('original_name', 'like', '%' . $search . '%');
    }
}
