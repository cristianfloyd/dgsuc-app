<?php

namespace App\Models;

use App\Contracts\OrigenRepositoryInterface;
use FTP\Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrigenesModel extends Model implements OrigenRepositoryInterface
{
    use HasFactory;
    protected $table = 'suc.origenes_models';
    protected $connection = 'pgsql-mapuche';

    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Encuentra un registro de origen por su ID.
     *
     * @param int $id El ID del registro de origen a buscar.
     * @return \App\Models\OrigenesModel|null El registro de origen encontrado, o null si no se encuentra.
     */
    public function findById(int $id): ?OrigenesModel
    {
        return $this->find($id);
    }

    public function findByName(string $name): ?OrigenesModel
    {
        return $this->where('name', $name)->first();
    }
}
