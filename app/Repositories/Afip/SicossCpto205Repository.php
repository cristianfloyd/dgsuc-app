<?php

declare(strict_types=1);

namespace App\Repositories\Afip;

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;

class SicossCpto205Repository
{
    use MapucheConnectionTrait;
    
    /**
     * Elimina la tabla temporal si existe
     */
    public function eliminarTablaTemporal(): void
    {
        DB::connection($this->getConnectionName())->statement("DROP TABLE IF EXISTS tcpto205");
    }
    
    /**
     * Crea una tabla temporal con los datos calculados para el concepto 205
     *
     * @param array $liquidaciones Lista de liquidaciones a procesar
     * @return int Número de registros procesados
     */
    public function crearTablaTemporal(array $liquidaciones): int
    {
        $this->eliminarTablaTemporal();
        
        // Crear tabla temporal con los datos calculados
        DB::connection($this->getConnectionName())->statement(
            $this->generarQueryCreacionTabla($liquidaciones)
        );
        
        return $this->contarRegistros();
    }
    
    /**
     * Cuenta los registros en la tabla temporal
     *
     * @return int Número de registros
     */
    public function contarRegistros(): int
    {
        $resultado = DB::connection($this->getConnectionName())->selectOne("SELECT COUNT(*) as total FROM tcpto205");
        return (int) $resultado->total;
    }
    
    /**
     * Genera la consulta SQL para crear la tabla temporal
     *
     * @param array $liquidaciones Lista de liquidaciones a incluir en la consulta
     * @return string Consulta SQL
     */
    private function generarQueryCreacionTabla(array $liquidaciones): string
    {
        $liquidacionesStr = implode(',', $liquidaciones);
        
        return "
            SELECT d21.nro_legaj, c.cuil, ((SUM(d21.impp_conce) * 100) / 2)::NUMERIC(10, 2) AS monto
            INTO TEMP tcpto205
            FROM mapuche.dh21 d21,
                mapuche.vdh01 c
            WHERE d21.nro_liqui IN ({$liquidacionesStr})
                AND d21.nro_legaj = c.nro_legaj
                AND d21.codn_conce = 789
                AND d21.nro_legaj IN
                    (SELECT DISTINCT nro_legaj FROM mapuche.dh21 WHERE nro_liqui IN ({$liquidacionesStr}) AND codn_conce = '205')
                AND d21.nro_legaj IN (SELECT b.nro_legaj
                                        FROM suc.control_aportes_diferencias a,
                                            mapuche.vdh01 b
                                        WHERE a.cuil = b.cuil)
            GROUP BY d21.nro_legaj, c.cuil
            ORDER BY nro_legaj
        ";
    }
    
    /**
     * Inicia una transacción en la conexión Mapuche
     */
    public function iniciarTransaccion(): void
    {
        DB::connection($this->getConnectionName())->beginTransaction();
    }
    
    /**
     * Confirma una transacción en la conexión Mapuche
     */
    public function confirmarTransaccion(): void
    {
        DB::connection($this->getConnectionName())->commit();
    }
    
    /**
     * Revierte una transacción en la conexión Mapuche
     */
    public function revertirTransaccion(): void
    {
        DB::connection($this->getConnectionName())->rollBack();
    }
} 