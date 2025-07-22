<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
