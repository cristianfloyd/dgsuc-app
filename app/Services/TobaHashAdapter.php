<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Implementación moderna de la clase toba_hash para Laravel optimizada para PHP 8.3
 * Replica la funcionalidad de Toba para el manejo de passwords usando características modernas de PHP
 * 
 * @package App\Services
 * @author Integración Laravel-Toba
 * @version 2.0 - PHP 8.3
 */
class TobaHashAdapter
{
    private const int MIN_ROUNDS = 10;
    private const array BCRYPT_INDICATORS = ['$2y$', '$2a$', '$2x$'];
    private const string ITOA64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Constructor de la clase
     */
    public function __construct(
        private readonly string $metodo = 'bcrypt',
        private int $rounds = self::MIN_ROUNDS
    ) {}

    /**
     * Establece el número de rondas para el hash
     */
    public function setCiclos(int $nro): void
    {
        $this->rounds = max($nro, self::MIN_ROUNDS);
    }

    /**
     * Genera un hash para una contraseña
     * 
     * @throws Exception Si no se pudo crear el hash
     */
    public function hash(string $input): string
    {
        $hash = crypt($input, $this->getSalt());
        
        if (strlen($hash) <= 13) {
            throw new Exception('Se produjo un error al crear el hash');
        }
        
        return $hash;
    }

    /**
     * Genera un hash para verificación usando un hash existente
     */
    public function getHashVerificador(string $input, string $existingHash): string
    {
        return crypt($input, $existingHash);
    }

    /**
     * Verifica si una contraseña coincide con un hash
     */
    public function verify(string $input, string $existingHash): bool
    {
        $hash = crypt($input, $existingHash);
        return hash_equals($hash, $existingHash);
    }

    /**
     * Genera una sal según el método de hash especificado
     */
    private function getSalt(): string
    {
        $salt = match (strtoupper($this->metodo)) {
            'BCRYPT' => '$2y$' . sprintf('%02d$', $this->rounds),
            'SHA512' => sprintf('$6$rounds=%d$', $this->calculateRounds()),
            'SHA256' => sprintf('$5$rounds=%d$', $this->calculateRounds()),
            'MD5' => '$1$',
            default => throw new Exception("Algoritmo de hash no soportado: {$this->metodo}")
        };

        $bytes = $this->getSecureRandomBytes(16);
        return $salt . $this->encodeBytes($bytes);
    }

    /**
     * Calcula las rondas para SHA256/SHA512
     */
    private function calculateRounds(): int
    {
        return $this->rounds < 1000 ? $this->rounds * 1000 : $this->rounds + 5000;
    }

    /**
     * Genera bytes aleatorios de manera segura usando características modernas de PHP
     * @param int $count
     */
    private function getSecureRandomBytes(int $count): string
    {
        try {
            // PHP 8.3 tiene random_bytes optimizado y siempre disponible
            $randomBytes = \random_bytes($count);
            return $randomBytes;
        } catch (Exception $e) {
            // Fallback extremo (muy improbable en PHP 8.3)
            Log::warning('random_bytes() falló, usando fallback', ['error' => $e->getMessage()]);
            return $this->legacyRandomBytes($count);
        }
    }

    /**
     * Fallback para generación de bytes aleatorios (solo para casos extremos)
     */
    private function legacyRandomBytes(int $count): string
    {
        $bytes = '';
        
        // Intentar /dev/urandom primero
        if (is_readable('/dev/urandom')) {
            $handle = fopen('/dev/urandom', 'rb');
            if ($handle !== false) {
                $bytes = fread($handle, $count);
                fclose($handle);
                
                if ($bytes !== false && strlen($bytes) === $count) {
                    return $bytes;
                }
            }
        }

        // Último recurso usando microtime
        $randomState = microtime() . getmypid();
        $bytes = '';
        
        for ($i = 0; $i < $count; $i += 16) {
            $randomState = hash('sha256', microtime() . $randomState);
            $bytes .= hash('sha256', $randomState, true);
        }

        return substr($bytes, 0, $count);
    }

    /**
     * Codifica bytes usando el alfabeto estándar para password hashing
     * Optimizado para PHP 8.3
     */
    private function encodeBytes(string $input): string
    {
        $output = '';
        $inputLength = strlen($input);
        
        for ($i = 0; $i < $inputLength; $i += 3) {
            $c1 = ord($input[$i]);
            $output .= self::ITOA64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            
            if ($i + 1 >= $inputLength) {
                $output .= self::ITOA64[$c1];
                break;
            }

            $c2 = ord($input[$i + 1]);
            $c1 |= $c2 >> 4;
            $output .= self::ITOA64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            if ($i + 2 >= $inputLength) {
                $output .= self::ITOA64[$c1];
                break;
            }

            $c3 = ord($input[$i + 2]);
            $c1 |= $c3 >> 6;
            $output .= self::ITOA64[$c1];
            $output .= self::ITOA64[$c3 & 0x3f];
        }
        
        return $output;
    }

    /**
     * Obtiene el método de hash actual
     */
    public function getMetodo(): string
    {
        return $this->metodo;
    }

    /**
     * Obtiene el número de rondas actual
     */
    public function getRounds(): int
    {
        return $this->rounds;
    }

    /**
     * Determina si un hash fue generado con un método específico
     */
    public function esHashDelMetodo(string $hash, string $metodo): bool
    {
        return match (strtoupper($metodo)) {
            'BCRYPT' => $this->isBcryptHash($hash),
            'SHA256' => str_starts_with($hash, '$5$'),
            'SHA512' => str_starts_with($hash, '$6$'),
            'MD5' => str_starts_with($hash, '$1$'),
            default => false
        };
    }

    /**
     * Verifica si un hash es de tipo bcrypt
     */
    private function isBcryptHash(string $hash): bool
    {
        return in_array(substr($hash, 0, 4), self::BCRYPT_INDICATORS, true);
    }

    /**
     * Verifica si el algoritmo especificado es soportado
     */
    public function esSoportado(string $algoritmo): bool
    {
        return in_array(strtoupper($algoritmo), ['BCRYPT', 'SHA256', 'SHA512', 'MD5'], true);
    }

    /**
     * Obtiene información del hash incluyendo algoritmo y configuración
     * 
     * @return array{algoritmo: string, rounds?: int, valido: bool}
     */
    public function analizarHash(string $hash): array
    {
        if ($this->isBcryptHash($hash)) {
            preg_match('/^\$2[axy]\$(\d{2})\$/', $hash, $matches);
            return [
                'algoritmo' => 'bcrypt',
                'rounds' => isset($matches[1]) ? (int)$matches[1] : null,
                'valido' => strlen($hash) === 60
            ];
        }

        if (str_starts_with($hash, '$5$')) {
            preg_match('/^\$5\$(?:rounds=(\d+)\$)?/', $hash, $matches);
            return [
                'algoritmo' => 'sha256',
                'rounds' => isset($matches[1]) ? (int)$matches[1] : 5000,
                'valido' => strlen($hash) > 20
            ];
        }

        if (str_starts_with($hash, '$6$')) {
            preg_match('/^\$6\$(?:rounds=(\d+)\$)?/', $hash, $matches);
            return [
                'algoritmo' => 'sha512',
                'rounds' => isset($matches[1]) ? (int)$matches[1] : 5000,
                'valido' => strlen($hash) > 20
            ];
        }

        if (str_starts_with($hash, '$1$')) {
            return [
                'algoritmo' => 'md5',
                'valido' => strlen($hash) === 34
            ];
        }

        return [
            'algoritmo' => 'desconocido',
            'valido' => false
        ];
    }

    /**
     * Genera un hash usando el algoritmo más seguro disponible (bcrypt por defecto)
     */
    public static function hashSeguro(string $password, int $rounds = 12): string
    {
        $hasher = new self('bcrypt', $rounds);
        return $hasher->hash($password);
    }

    /**
     * Verificación rápida estática
     */
    public static function verificarRapido(string $password, string $hash): bool
    {
        return hash_equals(crypt($password, $hash), $hash);
    }
}