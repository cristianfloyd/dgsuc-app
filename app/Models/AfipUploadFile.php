<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfipUploadFile extends Model
{
    protected $connection ='pgsql-mapuche';
    use HasFactory;
    protected $table = 'afip_upload_files';
    protected $fillable = [
        'id',
        
    ];
}
