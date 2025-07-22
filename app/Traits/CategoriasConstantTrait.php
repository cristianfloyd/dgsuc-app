<?php

namespace App\Traits;

/**
 * Trait para manejar categorías de cargos.
 *
 * @method bool hasCategory(string $category)
 * @method array getCategoriesByGroup(string $group)
 * @method string|null getGroupByCategory(string $category)
 */
trait CategoriasConstantTrait
{
    /**
     * Categorías agrupadas por tipo
     * DOCS: Categorías docentes secundarios
     * DOC2: Categorías Preuniversitarios
     * DOCU: Categorías docentes universitarias
     * AUTS: Categorías autoridades secundarias
     * AUTU: Categorías autoridades universitarias
     * NODO: Categorías no docentes.
     */
    public const CATEGORIAS = [
        'DOCS' => [
            'HOME', 'PEO6', 'ACPN', 'AYCP', 'AYEO', 'ATTP', 'JTPM', 'PR15', 'JTEP', 'JGEP',
            'MEPS', 'MEPR', 'MC20', 'MENI', 'MEPI', 'MJMA', 'PREO', 'ASPE', 'BIPH', 'BIBL',
            'JBIB', 'JEPR', 'MCOR', 'PR25', 'REG1', 'BI30', 'HOLU',
        ],
        'DOC2' => [ 'AYCP', 'SRG3', 'REG1', 'HOME', 'PR15', 'PR25', 'SRE1', 'SREG', 'BIBL', 'AYEO',
            'PREO', 'HOLU', 'RESE', 'VRSE', 'MEPR', 'MEPS', 'JGEP',
            'DI40', 'VD30', 'PEO6', 'MC20', 'ATTP', 'BI30', 'MCOR', 'ASPE', 'SECR', 'MENI',
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
            'VD20', 'PRSE', 'RESE', 'SECR', 'SREG', 'SRE1', 'VRSE', 'VD30', 'VD35', 'DI40',
        ],
        'AUTU' => [
            'DECC', 'DECE', 'DECP', 'RECT', 'SEFC', 'SEFE', 'SEFP', 'SEUC', 'SEUE',
            'SEUP', 'SFHP', 'SSUN', 'SUHE', 'VDPH', 'VICC', 'VIDC', 'VIDE', 'VIDP',
            'VIPH', 'VIRC', 'VIRE', 'VIRP',

        ],
        'NODO' => [
            '1', '2', '3', '4', '5', '6', '7', 'DOCA', 'DOCC', 'DOCE',
            'ESTI', 'INVE', 'MAEA', 'MAEE', 'MAEO',
        ],
    ];

    /**
     * Verifica si existe una categoría.
     */
    public function hasCategory(string $category): bool
    {
        foreach (self::CATEGORIAS as $group) {
            if (\in_array($category, $group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene todas las categorías de un grupo.
     *
     * @throws \InvalidArgumentException si el grupo no existe
     */
    public function getCategoriesByGroup(string $group): array
    {
        if (!\array_key_exists($group, self::CATEGORIAS)) {
            throw new \InvalidArgumentException("Grupo de categorías '$group' no existe");
        }
        return self::CATEGORIAS[$group];
    }

    /**
     * Obtiene el grupo al que pertenece una categoría.
     */
    public function getGroupByCategory(string $category): ?string
    {
        foreach (self::CATEGORIAS as $group => $categories) {
            if (\in_array($category, $categories)) {
                return $group;
            }
        }
        return null;
    }
}
