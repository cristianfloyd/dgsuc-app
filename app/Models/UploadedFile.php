<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
    ];


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
