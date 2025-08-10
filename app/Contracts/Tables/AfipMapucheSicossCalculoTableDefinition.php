<?php

declare(strict_types=1);

namespace App\Contracts\Tables;

class AfipMapucheSicossCalculoTableDefinition implements AbstractTableDefinitionInterface
{
    public function getTableName(): string
    {
        return 'suc.afip_mapuche_sicoss_calculos';
    }

    public function getColumns(): array
    {
        return [
            'id' => [
                'type' => 'bigIncrements',
                'primary' => true,
            ],
            'periodo_fiscal' => [
                'type' => 'string',
                'length' => 6,
                'nullable' => false,
            ],
            'cuil' => [
                'type' => 'string',
                'length' => 11,
                'nullable' => false,
            ],
            'remtotal' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => true,
            ],
            'rem1' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => true,
            ],
            'rem2' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => true,
            ],
            'aportesijp' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'aporteinssjp' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'contribucionsijp' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'contribucioninssjp' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'aportediferencialsijp' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'aportesres33_41re' => [
                'type' => 'decimal',
                'precision' => 15,
                'scale' => 2,
                'nullable' => false,
            ],
            'codc_uacad' => [
                'type' => 'string',
                'length' => 3,
                'fixed' => true,
                'nullable' => true,
            ],
            'caracter' => [
                'type' => 'string',
                'length' => 4,
                'fixed' => true,
                'nullable' => true,
            ],
        ];
    }

    public function getIndexes(): array
    {
        return [
            'primary' => ['id'],
            'idx_cuil_unique' => ['cuil'],
            'idx_periodo_fiscal_cuil' => ['periodo_fiscal', 'cuil'],
            'idx_uacad_caracter' => ['codc_uacad', 'caracter'],
        ];
    }
}
