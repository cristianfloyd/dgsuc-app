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
            'HOME','PEO6','ACPN','AYCP','AYEO','ATTP','JTPM','PR15','JTEP','JGEP','MEPS','MEPR','MC20','MENI',
            'MEPI','MJMA','PREO','ASPE','BIPH','BIBL','JBIB','JEPR','MCOR','PR25','REG1','BI30','HOLU',
        ],
        'DOCU' => [
            'A1EH','AY1E','JTEH','JTPE','ADEH','ASEH','TIEH','ADJE','ASOE','TITE','TIAE','A1PH','A2PH','AY1P','AY2P','JTPH','JTPP','ADPH','ASPH','TIPH','ADJP','ASOP','TITP','TIAP','A1SH','AY1S','JTSH','JTPS','ADSH','ASSH','TISH','ADJS','ASOS','TITS','TIAS','HOCO','HODI','HOJE','HOSU',
        ],
        'AUTS' => [
            'VD20','PRSE','RESE','SECR','SREH','SREG','SRE1','SRG3','VRSE','VD30','VD35','DI40',
        ],
        'AUTU' => [
            'DECC','SEFC','SEUC','VICC','VIDC','VIRC','DECE','RECT','SUHE','SEFE','SEUE',
            'SSUN','VIDE','VIRE','DECP','SFHP','SEFP','SEUP','VDPH','VIPH','VIDP','VIRP',
        ],
        'NODO' => [
            '1','2','3','4','5','6','7','DOCA','DOCC','DOCE',
            'ESTI','INVE','MAEA','MAEE','MAEO',
        ],
    ];
}
