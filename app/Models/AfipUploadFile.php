<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfipUploadFile extends Model
{
    use MapucheConnectionTrait;
    use HasFactory;

    protected $table = 'suc.afip_upload_files';

    protected $fillable = [
        'id',

    ];
}
