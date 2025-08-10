<?php

namespace App\Contracts\Tables;

class OrdenesDescuentoTableDefinition implements AbstractTableDefinitionInterface
{
    public const TABLE = 'suc.rep_ordenes_descuento';
    public const SCHEMA = 'suc';


    // Definición de columnas
    public const  COLUMNS = [
        'id' => [
            'type' => 'bigIncrements', // Cambiamos a bigIncrements para ID de Laravel
            'primary' => true,
            'unsigned' => true,
        ],
        'nro_liqui' => ['type' => 'integer'],
        'desc_liqui' => ['type' => 'string'],
        'codc_uacad' => ['type' => 'string'],
        'desc_item' => ['type' => 'string'],
        'codn_funci' => ['type' => 'integer'],
        'caracter' => ['type' => 'string'],
        'tipoescalafon' => ['type' => 'string'],
        'codn_fuent' => ['type' => 'string'],
        'nro_inciso' => ['type' => 'string'],
        'codn_progr' => ['type' => 'string'],
        'codn_conce' => ['type' => 'integer'],
        'desc_conce' => ['type' => 'string'],
        'impp_conce' => ['type' => 'decimal', 'precision' => 15, 'scale' => 2],
        'last_sync' => ['type' => 'timestamp'],
    ];


    public const INDEXES = [
        'codc_uacad' => ['codc_uacad'],
        'codn_conce' => ['codn_conce'],
    ];

    public function getTableName(): string
    {
        return self::SCHEMA . '.' . self::TABLE;
    }

    public function getColumns(): array
    {
        return self::COLUMNS;
    }

    public function getIndexes(): array
    {
        return self::INDEXES;
    }
}
