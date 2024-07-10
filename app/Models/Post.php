<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $connection ='pgsql-mapuche';
    protected $table = 'posts';
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'image_path',
        'is_published',
    ];
    /**
     * Devuelve la categoría a la que pertenece este post.
     * Relacion uno a muchos inversa
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Devuelve los tags asociados a este post.
     * Relacion muchos a muchos
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * */
     public function tags()
     {
        return $this->belongsToMany(Tags::class);
     }
}
