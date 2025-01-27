<?php

namespace App\Tables\Definitions;

use App\Contracts\Tables\AbstractTableDefinitionInterface;

class AfipMapucheSicossTableDefinition implements AbstractTableDefinitionInterface
{
    public const string TABLE = 'afip_mapuche_sicoss';
    public const string SCHEMA = 'suc';

    // Definición de columnas basada en el modelo de negocio sicoss.php
    public const array COLUMNS = [
        'id' => [
            'type' => 'bigIncrements',
            'primary' => true,
            'unsigned' => true
        ],
        // Datos de identificación
        'periodo_fiscal' => [
            'type' => 'string',
            'length' => 6,
            'index' => true
        ],
        'cuil' => [
            'type' => 'string',
            'length' => 11,
            'index' => true
        ],
        'apnom' => [
            'type' => 'string',
            'length' => 40
        ],

        // Datos familiares y situación
        'conyuge' => [
            'type' => 'boolean',
            'default' => false
        ],
        'cant_hijos' => [
            'type' => 'integer'
        ],
        'cod_situacion' => [
            'type' => 'integer',
            'length' => 2
        ],
        'cod_cond' => [
            'type' => 'integer',
            'length' => 2
        ],
        'cod_act' => [
            'type' => 'integer',
            'length' => 3
        ],
        'cod_zona' => [
            'type' => 'integer',
            'length' => 2
        ],

        // Datos de aportes y obra social
        'porc_aporte' => [
            'type' => 'decimal',
            'precision' => 5,
            'scale' => 2
        ],
        'cod_mod_cont' => [
            'type' => 'integer',
            'length' => 3
        ],
        'cod_os' => [
            'type' => 'string',
            'length' => 6
        ],
        'cant_adh' => [
            'type' => 'integer',
            'length' => 2
        ],

        // Campos monetarios
        'rem_total' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'rem_impo1' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'asig_fam_pag' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2
        ],
        'aporte_vol' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2
        ],
        'imp_adic_os' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2,
            'nullable' => true
        ],
        'exc_aport_ss' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2,
            'nullable' => true
        ],
        'exc_aport_os' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2,
            'nullable' => true
        ],
        'prov' => [
            'type' => 'string',
            'length' => 50,
            'nullable' => true
        ],
        'rem_impo2' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'rem_impo3' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'rem_impo4' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],

        // Datos de siniestros y reducciones
        'cod_siniestrado' => [
            'type' => 'integer',
            'length' => 2,
            'nullable' => true
        ],
        'marca_reduccion' => [
            'type' => 'integer',
            'length' => 1
        ],
        'recomp_lrt' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2,
            'nullable' => true
        ],

        // Datos de empresa y régimen
        'tipo_empresa' => [
            'type' => 'integer',
            'length' => 1
        ],
        'aporte_adic_os' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2
        ],
        'regimen' => [
            'type' => 'integer',
            'length' => 1
        ],

        // Situaciones de revista
        'sit_rev1' => [
            'type' => 'integer',
            'length' => 2
        ],
        'dia_ini_sit_rev1' => [
            'type' => 'integer',
            'length' => 2
        ],
        'sit_rev2' => [
            'type' => 'integer',
            'length' => 2
        ],
        'dia_ini_sit_rev2' => [
            'type' => 'integer',
            'length' => 2
        ],
        'sit_rev3' => [
            'type' => 'integer',
            'length' => 2
        ],
        'dia_ini_sit_rev3' => [
            'type' => 'integer',
            'length' => 2
        ],

        // Conceptos salariales
        'sueldo_adicc' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'sac' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'horas_extras' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'zona_desfav' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'vacaciones' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],

        // Datos laborales adicionales
        'cant_dias_trab' => [
            'type' => 'integer',
            'length' => 9
        ],
        'rem_impo5' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'convencionado' => [
            'type' => 'integer',
            'length' => 1
        ],
        'rem_impo6' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'tipo_oper' => [
            'type' => 'integer',
            'length' => 1,
            'nullable' => true
        ],
        'adicionales' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'premios' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'rem_dec_788_05' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'rem_imp7' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'nro_horas_ext' => [
            'type' => 'integer',
            'length' => 3
        ],
        'cpto_no_remun' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'maternidad' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'rectificacion_remun' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2
        ],
        'rem_imp9' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2,
            'nullable' => true
        ],
        'contrib_dif' => [
            'type' => 'decimal',
            'precision' => 9,
            'scale' => 2,
            'nullable' => true
        ],
        'hstrab' => [
            'type' => 'integer',
            'length' => 3
        ],
        'seguro' => [
            'type' => 'integer',
            'length' => 1
        ],
        'ley_27430' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'incsalarial' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
        'remimp11' => [
            'type' => 'decimal',
            'precision' => 12,
            'scale' => 2
        ],
    ];


    // Índices necesarios para optimizar consultas
    public const array INDEXES = [
        'periodo_fiscal_cuil_idx' => ['periodo_fiscal', 'cuil'],
        'cuil_idx' => ['cuil'],
        'periodo_fiscal_idx' => ['periodo_fiscal']
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
