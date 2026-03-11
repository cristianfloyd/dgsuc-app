<?php

namespace App\Models;

use App\Contracts\OrigenRepositoryInterface;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

class OrigenesModel extends Model implements OrigenRepositoryInterface
{
    use HasFactory;
    use MapucheConnectionTrait;

    protected $table = 'suc.origenes_models';

    protected $fillable = [
        'id',
        'name',
    ];

    #[Override]
    public static function boot(): void
    {
        parent::boot();
        self::verificarYCrearTabla();
    }

    public static function connectionName(): string
    {
        return new static()->getConnectionName();
    }

    /**
     * Verifica si la tabla existe y la crea si no existe.
     */
    public static function verificarYCrearTabla(): void
    {
        $builder = DB::connection(self::connectionName())->getSchemaBuilder();

        if (!$builder->hasTable('suc.origenes_models')) {
            $builder->create('suc.origenes_models', function ($table): void {
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
