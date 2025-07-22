<?php

namespace App\Services;

use App\Models\LiquidacionControl;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LiquidacionControlService
{
    use MapucheConnectionTrait;

    protected \Illuminate\Database\Connection $connection;

    public function __construct()
    {
        $this->connection = $this->getConnectionFromTrait();
    }

    /**
     * Obtiene el conteo de controles por estado de manera segura.
     *
     * @param string $estado Estado de los controles a contar
     *
     * @return int Número de controles en el estado especificado
     */
    public function getControlCountByState(string $estado): int
    {
        try {
            // Verificar si la tabla existe antes de realizar la consulta
            if (!LiquidacionControl::tableExists()) {
                Log::warning('La tabla suc.controles_lquidacion no existe en la base de datos');
                return 0;
            }

            return LiquidacionControl::where('estado', $estado)->count();
        } catch (\Exception $e) {
            Log::error("Error al obtener controles {$estado}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 0;
        }
    }

    /**
     * Obtiene el conteo de controles pendientes.
     *
     * @return int Número de controles pendientes
     */
    public function getPendingControlsCount(): int
    {
        return $this->getControlCountByState('pendiente');
    }

    /**
     * Obtiene el conteo de controles con error.
     *
     * @return int Número de controles con error
     */
    public function getErrorControlsCount(): int
    {
        return $this->getControlCountByState('error');
    }

    /**
     * Obtiene el conteo de controles completados.
     *
     * @return int Número de controles completados
     */
    public function getCompletedControlsCount(): int
    {
        return $this->getControlCountByState('completado');
    }

    /**
     * Controla negativos en una liquidación.
     *
     * @param int $nroLiqui Número de liquidación
     *
     * @return object Resultado del control
     */
    public function controlarNegativos(int $nroLiqui): object
    {
        try {
            $result = $this->connection->select('
                select nro_legaj, nro_cargo,
                sum(case when codn_conce >= 100 and codn_conce < 200 then impp_conce
                         when codn_conce >= 200 and codn_conce < 300 then impp_conce * -1
                    end)::numeric::money as Neto
                from mapuche.dh21
                where nro_liqui = ?
                group by nro_legaj, nro_cargo
                having sum(case when codn_conce >= 100 and codn_conce < 200 then impp_conce
                               when codn_conce >= 200 and codn_conce < 300 then impp_conce * -1
                          end)::numeric::money < 0::money
            ', [$nroLiqui]);

            return (object)[
                'success' => \count($result) === 0,
                'message' => \count($result) > 0 ? 'Se encontraron ' . \count($result) . ' cargos con neto negativo' : 'No se encontraron netos negativos',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error("Error al controlar negativos para liquidación #{$nroLiqui}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return (object)[
                'success' => false,
                'message' => 'Error al controlar negativos: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Controla cargos liquidados en una liquidación.
     *
     * @param int $nroLiqui Número de liquidación
     *
     * @return object Resultado del control
     */
    public function controlarCargosLiquidados(int $nroLiqui): object
    {
        try {
            $result = $this->connection->select('
                select a.nro_legaj, a.nro_cargo, c.codigoescalafon, a.codc_agrup,
                       a.codc_categ, a.codc_carac, c.desc_categ
                from mapuche.dh03 a, mapuche.dh21 b, mapuche.dh11 c
                where a.nro_legaj = b.nro_legaj
                and a.nro_cargo = b.nro_cargo
                and a.codc_categ = c.codc_categ
                and nro_liqui = ?
                group by a.nro_legaj, a.nro_cargo, c.codigoescalafon,
                         a.codc_agrup, a.codc_categ, a.codc_carac, c.desc_categ
            ', [$nroLiqui]);

            return (object)[
                'success' => \count($result) > 0,
                'message' => 'Se encontraron ' . \count($result) . ' cargos liquidados',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error("Error al controlar cargos liquidados para liquidación #{$nroLiqui}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return (object)[
                'success' => false,
                'message' => 'Error al controlar cargos liquidados: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Crea la tabla de controles si no existe.
     *
     * @return bool Resultado de la operación
     */
    public function createTableIfNotExists(): bool
    {
        try {
            if (!Schema::hasTable('suc.controles_liquidacion')) {
                Schema::create('suc.controles_liquidacion', function ($table): void {
                    $table->id();
                    $table->integer('nro_liqui');
                    $table->string('nombre_control');
                    $table->enum('estado', ['pendiente', 'error', 'completado'])->default('pendiente');
                    $table->text('resultado')->nullable();
                    $table->json('datos_resultado')->nullable();
                    $table->timestamp('fecha_ejecucion')->nullable();
                    $table->string('ejecutado_por')->nullable();
                    $table->timestamps();

                    $table->index('nro_liqui');
                    $table->index('estado');
                });

                Log::info('Tabla suc.controles_liquidacion creada exitosamente');
                return true;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error al crear la tabla suc.controles_liquidacion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
