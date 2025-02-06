<?php

namespace App\Enums;

enum PuestoDesempenado: string
{
    case DIRECTIVO = '1120';
    case PROFESOR_UNIVERSITARIO = '2310';
    case PROFESOR_SECUNDARIO = '2320';
    case NODOCENTE = '4190';
    case MEDICO = '2221';
    case ENFERMERO = '2230';

    public function descripcion(): string
    {
        return match($this) {
            self::DIRECTIVO => 'Personal Directivo de la Adm. Pública',
            self::PROFESOR_UNIVERSITARIO => 'Profesores de Universidades',
            self::PROFESOR_SECUNDARIO => 'Profesores de Enseñanza Secundaria',
            self::NODOCENTE => 'Otros nodocentes',
            self::MEDICO => 'Médicos (H)',
            self::ENFERMERO => 'Enfermeros (S)',
        };
    }

    public function escalafon(): string
    {
        return match($this) {
            self::DIRECTIVO => 'Autoridades Superiores',
            self::PROFESOR_UNIVERSITARIO, self::PROFESOR_SECUNDARIO => 'Docentes',
            self::NODOCENTE => 'NoDocentes',
            self::MEDICO, self::ENFERMERO => 'Asistenciales',
        };
    }

    public static function fromCodigo(string $codigo): ?self
    {
        return match($codigo) {
            '1120' => self::DIRECTIVO,
            '2310' => self::PROFESOR_UNIVERSITARIO,
            '2320' => self::PROFESOR_SECUNDARIO,
            '4190' => self::NODOCENTE,
            '2221' => self::MEDICO,
            '2230' => self::ENFERMERO,
            default => null,
        };
    }

    public static function toArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $enum) {
            $carry[$enum->value] = [
                'codigo' => $enum->value,
                'descripcion' => $enum->descripcion(),
                'escalafon' => $enum->escalafon(),
            ];
            return $carry;
        }, []);
    }
}
