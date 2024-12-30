<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, MapucheConnectionTrait;

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
