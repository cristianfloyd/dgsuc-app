<?php

namespace App\Traits;

trait CategoriasConstantTrait
{
    /**
     * Constantes agrupadas para las categorías de cargos.
     * Estos valores corresponden a la columna codc_categ del modelo Dh11.
     */
    public const array CATEGORIAS = [
        'DOCS' => [
                    'HOME', 'PEO6', 'ACPN', 'AYCP', 'AYEO', 'ATTP', 'JTPM', 'PR15', 'JTEP', 'JGEP',
                    'MEPS', 'MEPR', 'MC20', 'MENI', 'MEPI', 'MJMA', 'PREO', 'ASPE', 'BIPH', 'BIBL',
                    'JBIB', 'JEPR', 'MCOR', 'PR25','REG1','BI30','HOLU',
        ],
        'DOC2' => [ 'AYCP', 'SRG3', 'REG1', 'HOME', 'PR15', 'PR25', 'SRE1', 'SREG', 'BIBL', 'AYEO' ,
                    'PREO', 'HOLU', 'RESE', 'VRSE', 'MEPR', 'MEPS', 'JGEP' ,
                    'DI40', 'VD30', 'PEO6', 'MC20', 'ATTP', 'BI30', 'MCOR', 'ASPE', 'SECR', 'MENI' ,
                    'ACPN', 'JTEP', 'MEPI', 'JEPR', 'PRSE', 'MJMA', 'JTPM', 'JBIB',
                    'VD35', 'VD20',
                    'HODI', 'HOJE', 'HOCO',
        ],
        'DOCU' => [
                    'A1EH', 'AY1E', 'JTEH', 'JTPE', 'ADEH', 'ASEH', 'TIEH', 'ADJE', 'ASOE', 'TITE',
                    'TIAE', 'A1PH', 'A2PH', 'AY1P', 'AY2P', 'JTPH', 'JTPP', 'ADPH', 'ASPH', 'TIPH',
                    'ADJP', 'ASOP', 'TITP', 'TIAP', 'A1SH', 'AY1S', 'JTSH', 'JTPS', 'ADSH', 'ASSH',
                    'TISH', 'ADJS', 'ASOS', 'TITS', 'TIAS', 'HOSU',
        ],
        'AUTS' => [
            'VD20','PRSE','RESE','SECR','SREG','SRE1','VRSE','VD30','VD35','DI40',
        ],
        'AUTU' => [
            'DECC', 'DECE', 'DECP', 'RECT', 'SEFC', 'SEFE', 'SEFP', 'SEUC', 'SEUE',
            'SEUP', 'SFHP', 'SSUN', 'SUHE', 'VDPH', 'VICC', 'VIDC', 'VIDE', 'VIDP',
            'VIPH', 'VIRC', 'VIRE', 'VIRP',

        ],
        'NODO' => [
            '1','2','3','4','5','6','7','DOCA','DOCC','DOCE',
            'ESTI','INVE','MAEA','MAEE','MAEO',
        ],
    ];
}

