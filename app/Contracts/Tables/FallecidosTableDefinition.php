<?php

declare(strict_types=1);

namespace App\Contracts\Tables;

class FallecidosTableDefinition implements AbstractTableDefinitionInterface
{
    public function getTableName(): string
    {
        return 'suc.rep_fallecidos';
    }

    public function getColumns(): array
    {
        return [
            'id' => [
                'type' => 'bigIncrements', // Cambiamos a bigIncrements para ID de Laravel
                'primary' => true,
                'unsigned' => true,
            ],
            'nro_legaj' => ['type' => 'integer'],
            'apellido' => ['type' => 'string', 'length' => 20],
            'nombre' => ['type' => 'string', 'length' => 20],
            'cuil' => ['type' => 'string', 'length' => 11],
            'codc_uacad' => ['type' => 'string', 'length' => 4],
            'fec_defun' => ['type' => 'date', 'nullable' => true],
        ];
    }

    public function getIndexes(): array
    {
        return [
            'idx_fallecidos_legajo' => ['columns' => ['nro_legaj']],
            'idx_fallecidos_cuil' => ['columns' => ['cuil']],
        ];
    }
}
