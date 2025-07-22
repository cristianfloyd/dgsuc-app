<?php

namespace App\Models;

use App\Contracts\OrigenRepositoryInterface;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrigenesModel extends Model implements OrigenRepositoryInterface
{
    use HasFactory;
    use MapucheConnectionTrait;

    protected $table = 'suc.origenes_models';

    protected $fillable = [
        'id',
        'name',
    ];

    public static function boot(): void
    {
        parent::boot();
        self::verificarYCrearTabla();
    }

    public static function connectionName(): string
    {
        return (new static())->getConnectionName();
    }

    /**
     * Verifica si la tabla existe y la crea si no existe.
     *
     * @return void
     */
    public static function verificarYCrearTabla(): void
    {
        $schema = \Illuminate\Support\Facades\DB::connection(self::connectionName())->getSchemaBuilder();

        if (!$schema->hasTable('suc.origenes_models')) {
            $schema->create('suc.origenes_models', function ($table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    /**
     * Encuentra un registro de origen por su ID.
     *
     * @param int $id El ID del registro de origen a buscar.
     *
     * @return OrigenesModel|null El registro de origen encontrado, o null si no se encuentra.
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
