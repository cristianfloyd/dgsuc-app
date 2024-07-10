<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;
    protected $connection ='pgsql-mapuche';
    protected $table = 'tags';
    protected $fillable = [
        'name',
    ];

    // Relacion muchos a muchos
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
