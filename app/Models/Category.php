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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function posts()
    {
        return $this->hasMany('App\Models\Post'); // @phpstan-ignore argument.type
    }
}
