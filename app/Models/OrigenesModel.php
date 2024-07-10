<?php

namespace App\Models;

use FTP\Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrigenesModel extends Model
{
    use HasFactory;
    protected $table = 'suc.origenes_models';
    protected $connection = 'pgsql-mapuche';

    protected $fillable = [
        'id',
        'name',
     ];
}
