<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Afip\SicossLegacy;
use App\Models\Mapuche\MapucheConfig;
use App\Services\EnhancedDatabaseConnectionService;

class SicossTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'sicoss:test
                            {legajo : Número de legajo a procesar}
                            {--periodo= : Período fiscal (formato: YYYY-MM, ej: 2024-10)}
                            {--retro : Incluir períodos retroactivos}
                            {--licencias : Incluir procesamiento de licencias}
                            {--inactivos : Incluir agentes sin cargo activo}
                            {--seguro-vida : Incluir seguro de vida patronal}
                            {--export= : Exportar resultado a archivo JSON}
                            {--detailed : Mostrar información detallada}
                            {--clean-only : Solo limpiar tablas temporales y salir}
                            {--connection= : Conexión de base de datos a usar (pgsql-prod, pgsql-liqui, pgsql-desa, etc.)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando de testing para generar SICOSS de un legajo específico sin crear archivos TXT';

        /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Configurar conexión de base de datos si se especificó
            if ($connection = $this->option('connection')) {
                $this->configurarConexion($connection);
            }

            // Si solo queremos limpiar tablas y salir
            if ($this->option('clean-only')) {
                return $this->limpiarTablasTemporales();
            }

            $legajo = (int) $this->argument('legajo');

            if ($legajo <= 0) {
                $this->error('❌ El número de legajo debe ser un entero positivo');
                return 1;
            }

            $this->info("🚀 Iniciando test SICOSS para legajo: {$legajo}");
            $this->newLine();

            // Configurar período si se especificó
            if ($periodo = $this->option('periodo')) {
                $this->configurarPeriodo($periodo);
            }

            // Mostrar configuración actual
            $this->mostrarConfiguracion($legajo);

            // Limpiar tablas temporales
            $this->limpiarTablasTemporales();

            // Preparar datos de configuración
            $datos = $this->prepararDatos($legajo);

            // Ejecutar SICOSS
            $resultado = $this->ejecutarSicoss($datos);

            // Mostrar resultados
            $this->mostrarResultados($resultado, $legajo);

            // Exportar si se solicita
            if ($export = $this->option('export')) {
                $this->exportarResultados($resultado, $export);
            }

            $this->newLine();
            $this->info('✅ Proceso completado exitosamente');

            return 0;

        } catch (Exception $e) {
            $this->newLine();
            $this->error('❌ Error durante el proceso:');
            $this->error("Mensaje: {$e->getMessage()}");
            $this->error("Archivo: {$e->getFile()}:{$e->getLine()}");

            if ($this->option('detailed')) {
                $this->newLine();
                $this->error('Stack trace:');
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    /**
     * Configura la conexión de base de datos
     */
    protected function configurarConexion(string $connection): void
    {
        $service = app(EnhancedDatabaseConnectionService::class);
        $conexionesDisponibles = $service->getAvailableConnections();

        if (!array_key_exists($connection, $conexionesDisponibles)) {
            $this->error("❌ Conexión '{$connection}' no válida");
            $this->line('Conexiones disponibles:');
            foreach ($conexionesDisponibles as $key => $name) {
                $this->line("  - {$key} ({$name})");
            }
            exit(1);
        }

        // Establecer la conexión usando el servicio
        $service->setConnection($connection);

        $this->info("🔗 Conexión configurada: {$connection} ({$conexionesDisponibles[$connection]})");
        $this->newLine();
    }

    /**
     * Configura el período fiscal
     */
    protected function configurarPeriodo(string $periodo): void
    {
        if (!preg_match('/^(\d{4})-(\d{1,2})$/', $periodo, $matches)) {
            $this->error('❌ Formato de período inválido. Use: YYYY-MM (ej: 2024-10)');
            exit(1);
        }

        $año = (int) $matches[1];
        $mes = (int) $matches[2];

        if ($mes < 1 || $mes > 12) {
            $this->error('❌ El mes debe estar entre 1 y 12');
            exit(1);
        }

        // Temporalmente podemos usar variables de entorno o advertir al usuario
        // que debe configurar el período desde la interfaz de Mapuche
        $this->warn("⚠️  NOTA: Para cambiar el período fiscal, debe configurarlo desde la interfaz de Mapuche");
        $this->warn("   Período solicitado: {$mes}/{$año}");
        $this->warn("   Período actual del sistema: " . MapucheConfig::getMesFiscal() . "/" . MapucheConfig::getAnioFiscal());
        $this->newLine();
    }

    /**
     * Muestra la configuración actual
     */
    protected function mostrarConfiguracion(int $legajo): void
    {
        $service = app(EnhancedDatabaseConnectionService::class);
        $currentConnection = $service->getCurrentConnection();
        $connectionName = $service->getAvailableConnections()[$currentConnection] ?? $currentConnection;

        $this->info('📋 Configuración actual:');
        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Legajo', $legajo],
                ['Conexión BD', "{$currentConnection} ({$connectionName})"],
                ['Período Fiscal', MapucheConfig::getMesFiscal() . '/' . MapucheConfig::getAnioFiscal()],
                ['Código Reparto', MapucheConfig::getDatosCodcReparto() ?? '1 (por defecto)'],
                ['Períodos Retro', $this->option('retro') ? 'SÍ' : 'NO'],
                ['Licencias', $this->option('licencias') ? 'SÍ' : 'NO'],
                ['Agentes Inactivos', $this->option('inactivos') ? 'SÍ' : 'NO'],
                ['Seguro Vida', $this->option('seguro-vida') ? 'SÍ' : 'NO'],
            ]
        );
        $this->newLine();
    }

        /**
     * Limpia las tablas temporales
     */
    protected function limpiarTablasTemporales(): int
    {
        $this->info('🧹 Limpiando tablas temporales...');

        // Obtener la conexión actual
        $service = app(EnhancedDatabaseConnectionService::class);
        $currentConnection = $service->getCurrentConnection();

        $tablas = ['pre_conceptos_liquidados', 'conceptos_liquidados'];
        $limpiezas_exitosas = 0;

        foreach ($tablas as $tabla) {
            try {
                DB::connection($currentConnection)->statement("DROP TABLE IF EXISTS {$tabla} CASCADE");
                $this->line("   ✅ {$tabla} eliminada de {$currentConnection}");
                $limpiezas_exitosas++;
            } catch (Exception $e) {
                $this->line("   ⚠️  {$tabla}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Limpieza completada ({$limpiezas_exitosas}/" . count($tablas) . " tablas)");

        if ($this->option('clean-only')) {
            return 0;
        }

        $this->newLine();
        return $limpiezas_exitosas;
    }

    /**
     * Prepara los datos de configuración para SICOSS
     */
    protected function prepararDatos(int $legajo): array
    {
        return [
            'nro_legaj' => $legajo,
            'check_retro' => $this->option('retro') ? 1 : 0,
            'check_lic' => $this->option('licencias') ? 1 : 0,
            'check_sin_activo' => $this->option('inactivos') ? 1 : 0,
            'seguro_vida_patronal' => $this->option('seguro-vida') ? 1 : 0,
        ];
    }

    /**
     * Ejecuta el proceso SICOSS
     */
    protected function ejecutarSicoss(array $datos): array
    {
        $this->info('⚙️  Ejecutando proceso SICOSS...');

        $sicoss = app(SicossLegacy::class);

        return $sicoss->genera_sicoss($datos, '', '', true);
    }

    /**
     * Muestra los resultados del proceso
     */
    protected function mostrarResultados(array $resultado, int $legajo): void
    {
        $this->newLine();
        $this->info('📊 Resultados del procesamiento:');

        if (empty($resultado)) {
            $this->warn("⚠️  No se encontraron datos para el legajo {$legajo}");
            $this->line('Posibles causas:');
            $this->line('  - El legajo no existe o está inactivo');
            $this->line('  - No tiene liquidaciones en el período especificado');
            $this->line('  - No cumple con los filtros aplicados');
            return;
        }

        $this->info("✅ Legajos procesados: " . count($resultado));
        $this->newLine();

        $legajo_data = $resultado[0];

        // Información básica
        $this->info('👤 Información del Legajo:');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['CUIL', $legajo_data['cuil'] ?? 'N/A'],
                ['Nombre', $legajo_data['apyno'] ?? 'N/A'],
                ['Código Situación', $legajo_data['codigosituacion'] ?? 'N/A'],
                ['Días Trabajados', $legajo_data['dias_trabajados'] ?? 'N/A'],
                ['Obra Social', $legajo_data['codigo_os'] ?? 'N/A'],
                ['Hijos', $legajo_data['hijos'] ?? 0],
                ['Cónyuge', ($legajo_data['conyugue'] ?? false) ? 'SÍ' : 'NO'],
            ]
        );

        // Importes principales
        $this->newLine();
        $this->info('💰 Importes Principales:');
        $this->table(
            ['Concepto', 'Importe'],
            [
                ['Bruto', '$' . number_format($legajo_data['IMPORTE_BRUTO'] ?? 0, 2)],
                ['Imponible Principal', '$' . number_format($legajo_data['IMPORTE_IMPON'] ?? 0, 2)],
                ['Imponible Patronal', '$' . number_format($legajo_data['ImporteImponiblePatronal'] ?? 0, 2)],
                ['SAC', '$' . number_format($legajo_data['ImporteSAC'] ?? 0, 2)],
            ]
        );

        // Detalles adicionales si se solicita
        if ($this->option('detailed')) {
            $this->mostrarDetallesAdicionales($legajo_data);
        }
    }

    /**
     * Muestra detalles adicionales del legajo
     */
    protected function mostrarDetallesAdicionales(array $legajo_data): void
    {
        $this->newLine();
        $this->info('🔍 Detalles Adicionales:');
        $this->table(
            ['Concepto', 'Importe'],
            [
                ['Horas Extras', '$' . number_format($legajo_data['ImporteHorasExtras'] ?? 0, 2)],
                ['Zona Desfavorable', '$' . number_format($legajo_data['ImporteZonaDesfavorable'] ?? 0, 2)],
                ['Adicionales', '$' . number_format($legajo_data['ImporteAdicionales'] ?? 0, 2)],
                ['Imponible 4', '$' . number_format($legajo_data['ImporteImponible_4'] ?? 0, 2)],
                ['Imponible 5', '$' . number_format($legajo_data['ImporteImponible_5'] ?? 0, 2)],
                ['Imponible 6', '$' . number_format($legajo_data['ImporteImponible_6'] ?? 0, 2)],
                ['Imponible 9', '$' . number_format($legajo_data['importeimponible_9'] ?? 0, 2)],
            ]
        );

        // Mostrar datos completos si hay claves adicionales
        $campos_mostrados = ['cuil', 'apyno', 'codigosituacion', 'dias_trabajados', 'codigo_os', 'hijos', 'conyugue',
                            'IMPORTE_BRUTO', 'IMPORTE_IMPON', 'ImporteImponiblePatronal', 'ImporteSAC',
                            'ImporteHorasExtras', 'ImporteZonaDesfavorable', 'ImporteAdicionales',
                            'ImporteImponible_4', 'ImporteImponible_5', 'ImporteImponible_6', 'importeimponible_9'];

        $campos_adicionales = array_diff(array_keys($legajo_data), $campos_mostrados);

        if (!empty($campos_adicionales)) {
            $this->newLine();
            $this->info('📋 Campos Adicionales:');
            foreach ($campos_adicionales as $campo) {
                $valor = $legajo_data[$campo];
                if (is_numeric($valor) && $valor > 0) {
                    $this->line("  {$campo}: {$valor}");
                }
            }
        }
    }

    /**
     * Exporta los resultados a un archivo JSON
     */
    protected function exportarResultados(array $resultado, string $archivo): void
    {
        try {
            $service = app(EnhancedDatabaseConnectionService::class);
            $currentConnection = $service->getCurrentConnection();

            $datos_export = [
                'timestamp' => now()->toISOString(),
                'legajo' => $this->argument('legajo'),
                'periodo' => MapucheConfig::getMesFiscal() . '/' . MapucheConfig::getAnioFiscal(),
                'conexion_bd' => $currentConnection,
                'configuracion' => [
                    'retro' => $this->option('retro'),
                    'licencias' => $this->option('licencias'),
                    'inactivos' => $this->option('inactivos'),
                    'seguro_vida' => $this->option('seguro-vida'),
                    'connection' => $this->option('connection'),
                ],
                'resultado' => $resultado
            ];

            $json = json_encode($datos_export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($archivo, $json);

            $this->newLine();
            $this->info("📁 Resultados exportados a: {$archivo}");
            $this->line("   Tamaño: " . number_format(strlen($json)) . " bytes");

        } catch (Exception $e) {
            $this->error("❌ Error al exportar: {$e->getMessage()}");
        }
    }
}
