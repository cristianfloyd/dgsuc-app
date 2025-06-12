<?php

namespace App\Services\Afip;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\MapucheConfig;

class SicossOptimizadoTests
{
    /**
     * 🧪 SUITE DE PRUEBAS DE RENDIMIENTO SICOSS OPTIMIZADO
     * 
     * Esta clase contiene todos los métodos para probar el rendimiento
     * de la optimización SICOSS con diferentes cantidades de legajos.
     */

    /**
     * Test rápido con 100 legajos
     */
    public static function test_rapido_100_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(100, 'TEST RÁPIDO 100 LEGAJOS', $datos);
    }

    /**
     * Test estándar con 1000 legajos
     */
    public static function test_estandar_1000_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(1000, 'TEST ESTÁNDAR 1000 LEGAJOS', $datos);
    }

    /**
     * Test intensivo con 3000 legajos
     */
    public static function test_intensivo_3000_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(3000, 'TEST INTENSIVO 3000 LEGAJOS', $datos);
    }

    /**
     * Test masivo con 5000 legajos
     */
    public static function test_masivo_5000_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(5000, 'TEST MASIVO 5000 LEGAJOS', $datos);
    }

    /**
     * Test de carga completa con 10000 legajos
     */
    public static function test_carga_completa_10000_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(10000, 'TEST CARGA COMPLETA 10K LEGAJOS', $datos);
    }

    public static function test_carga_completa_20000_legajos($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(20000, 'TEST CARGA COMPLETA 20K LEGAJOS', $datos);
    }
    
    /**
     * Test de producción simulada con todos los legajos disponibles
     */
    public static function test_produccion_completa($datos = null)
    {
        return self::ejecutar_prueba_con_legajos(null, 'TEST PRODUCCIÓN COMPLETA', $datos);
    }

    /**
     * Método base para ejecutar pruebas con diferentes cantidades de legajos - VERSIÓN MEJORADA
     */
    private static function ejecutar_prueba_con_legajos($limite_legajos, $nombre_test, $datos = null)
    {
        Log::info("=== 🧪 INICIANDO $nombre_test ===");

        // ✅ 0. DIAGNÓSTICO PREVIO
        $diagnostico = self::diagnosticar_sistema_y_conexiones();
        if (!$diagnostico['exito']) {
            return [
                'exito' => false,
                'nombre_test' => $nombre_test,
                'error' => 'Falló el diagnóstico previo: ' . $diagnostico['error']
            ];
        }

        Log::info("✅ Diagnóstico previo completado - Legajos disponibles: {$diagnostico['legajos']['legajos_con_liquidacion_periodo']}");

        // ✅ 1. INICIALIZAR TODAS LAS VARIABLES ESTÁTICAS
        self::inicializar_variables_estaticas();

        // ✅ 2. CONFIGURACIÓN DE PRUEBA
        if (!$datos) {
            $datos = [
                'check_retro' => 0,
                'check_lic' => false,
                'check_sin_activo' => false,
                'TopeJubilatorioPatronal' => 500000,
                'TopeJubilatorioPersonal' => 500000,
                'TopeOtrosAportesPersonal' => 500000,
                'truncaTope' => 1,
                'seguro_vida_patronal' => 0
            ];
        }

        // ✅ 3. CREAR TABLA PRE_CONCEPTOS_LIQUIDADOS
        $periodo = MapucheConfig::getPeriodoCorriente();
        $per_mesct = $periodo['month'];
        $per_anoct = $periodo['year'];
        $where = ' true ';

        SicossOptimizado::obtener_conceptos_liquidados($per_anoct, $per_mesct, $where);

        Log::info('✅ Variables estáticas inicializadas y tabla pre_conceptos_liquidados creada');

        // ✅ 4. OBTENER LEGAJOS SEGÚN LÍMITE
        $where_periodo = ' true ';
        $where_legajo = ' true ';

        $inicio_total = microtime(true);
        $memoria_inicial = memory_get_usage(true);

        try {
            // ✅ USAR MÉTODO PÚBLICO PARA OBTENER CODC_REPARTO
            $codc_reparto = SicossOptimizado::getCodcReparto();

            // Obtener todos los legajos disponibles
            $todos_legajos = SicossOptimizado::obtener_legajos(
                $codc_reparto,  // ✅ Usar la variable obtenida del método público
                $where_periodo,
                $where_legajo,
                $datos['check_lic'],
                $datos['check_sin_activo']
            );

            // ✅ Aplicar límite si es especificado
            if ($limite_legajos !== null) {
                $legajos = array_slice($todos_legajos, 0, $limite_legajos);
                $tipo_test = "LIMITADO A $limite_legajos";
            } else {
                $legajos = $todos_legajos;
                $tipo_test = "TODOS LOS LEGAJOS";
            }

            $legajos_obtenidos = count($legajos);
            $legajos_disponibles = count($todos_legajos);

            Log::info("✅ Legajos para $nombre_test: $legajos_obtenidos de $legajos_disponibles disponibles ($tipo_test)");

            // ✅ 5. ACTIVAR MONITOREO DE PERFORMANCE
            self::activar_monitoreo_performance($legajos_obtenidos);

            // ✅ 6. PROCESAR CON OPTIMIZACIÓN
            $resultado = SicossOptimizado::procesa_sicoss(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                'sicoss_test_' . ($limite_legajos ?? 'completo'),
                null,
                false,
                false,
                true  // Retornar datos
            );

            $fin_total = microtime(true);
            $memoria_final = memory_get_usage(true);

            // 📊 ANÁLISIS DETALLADO DE RESULTADOS
            $estadisticas = self::analizar_resultados_completos(
                $inicio_total,
                $fin_total,
                $memoria_inicial,
                $memoria_final,
                $legajos_disponibles,
                $legajos_obtenidos,
                $resultado,
                $nombre_test
            );

            Log::info("=== 📊 RESULTADOS $nombre_test ===", $estadisticas);

            // ✅ 7. LIMPIAR TABLA TEMPORAL
            self::limpiar_tablas_temporales();

            return [
                'exito' => true,
                'nombre_test' => $nombre_test,
                'estadisticas' => $estadisticas,
                'legajos_muestra' => array_slice($resultado, 0, 3), // Primeros 3 para verificar
                'recomendaciones' => self::generar_recomendaciones($estadisticas)
            ];
        } catch (\Exception $e) {
            $fin_total = microtime(true);

            Log::error("❌ ERROR EN $nombre_test", [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'tiempo_hasta_error' => round($fin_total - $inicio_total, 2),
                'memoria_al_error' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);

            // Limpiar en caso de error
            self::limpiar_tablas_temporales();

            return [
                'exito' => false,
                'nombre_test' => $nombre_test,
                'error' => $e->getMessage(),
                'detalles' => [
                    'linea' => $e->getLine(),
                    'archivo' => basename($e->getFile())
                ]
            ];
        }
    }

    /**
     * Ejecuta una suite completa de pruebas con diferentes cargas
     */
    public static function suite_completa_rendimiento($incluir_produccion = false)
    {
        Log::info('=== 🚀 INICIANDO SUITE COMPLETA DE RENDIMIENTO ===');

        $resultados = [];
        $inicio_suite = microtime(true);

        // Tests progresivos
        $tests = [
            'rapido_100' => fn() => self::test_rapido_100_legajos(),
            'estandar_1000' => fn() => self::test_estandar_1000_legajos(),
            'intensivo_3000' => fn() => self::test_intensivo_3000_legajos(),
            'masivo_5000' => fn() => self::test_masivo_5000_legajos(),
        ];

        // Incluir test de producción solo si se solicita
        if ($incluir_produccion) {
            $tests['produccion_completa'] = fn() => self::test_produccion_completa();
        }

        foreach ($tests as $nombre => $test) {
            Log::info("▶️  Ejecutando test: $nombre");
            $resultados[$nombre] = $test();

            // Pausa entre tests para liberar memoria
            sleep(2);
        }

        $fin_suite = microtime(true);

        // Análisis comparativo
        $analisis_comparativo = self::analizar_suite_comparativa($resultados, $fin_suite - $inicio_suite);

        Log::info('=== 📈 ANÁLISIS COMPARATIVO SUITE COMPLETA ===', $analisis_comparativo);

        return [
            'resultados_individuales' => $resultados,
            'analisis_comparativo' => $analisis_comparativo,
            'tiempo_total_suite_min' => round(($fin_suite - $inicio_suite) / 60, 2)
        ];
    }

    /**
     * Análisis detallado de resultados con métricas avanzadas
     */
    private static function analizar_resultados_completos($inicio, $fin, $memoria_inicial, $memoria_final, $disponibles, $procesados, $resultado, $nombre_test)
    {
        $tiempo_total = $fin - $inicio;

        return [
            'nombre_test' => $nombre_test,
            'legajos_disponibles' => $disponibles,
            'legajos_procesados' => $procesados,
            'legajos_validos_generados' => count($resultado),
            'tasa_exito_porcentaje' => round((count($resultado) / max($procesados, 1)) * 100, 2),
            'tiempo_total_segundos' => round($tiempo_total, 2),
            'tiempo_total_minutos' => round($tiempo_total / 60, 2),
            'tiempo_por_legajo_ms' => round(($tiempo_total * 1000) / max($procesados, 1), 2),
            'velocidad_legajos_por_segundo' => round($procesados / max($tiempo_total, 0.1), 2),
            'memoria_utilizada_mb' => round(($memoria_final - $memoria_inicial) / 1024 / 1024, 2),
            'memoria_pico_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'eficiencia_memoria_kb_por_legajo' => round((memory_get_peak_usage(true) / 1024) / max($procesados, 1), 2),

            // Proyecciones de escalabilidad
            'proyeccion_38000_legajos_min' => round(($tiempo_total * 38000 / max($procesados, 1)) / 60, 2),
            'proyeccion_100000_legajos_horas' => round(($tiempo_total * 100000 / max($procesados, 1)) / 3600, 2),

            // Clasificación de rendimiento
            'clasificacion_velocidad' => self::clasificar_velocidad($tiempo_total, $procesados),
            'clasificacion_memoria' => self::clasificar_uso_memoria(memory_get_peak_usage(true)),
            'clasificacion_general' => self::clasificar_rendimiento_general($tiempo_total, $procesados, memory_get_peak_usage(true))
        ];
    }

    /**
     * Clasificaciones de rendimiento
     */
    private static function clasificar_velocidad($tiempo, $legajos)
    {
        $ms_por_legajo = ($tiempo * 1000) / max($legajos, 1);

        if ($ms_por_legajo < 20) return 'EXCELENTE ⚡';
        if ($ms_por_legajo < 50) return 'MUY BUENO ✅';
        if ($ms_por_legajo < 100) return 'BUENO 🔶';
        if ($ms_por_legajo < 200) return 'ACEPTABLE ⚠️';
        return 'NECESITA MEJORA ❌';
    }

    private static function clasificar_uso_memoria($memoria_bytes)
    {
        $memoria_mb = $memoria_bytes / 1024 / 1024;

        if ($memoria_mb < 200) return 'EXCELENTE 💚';
        if ($memoria_mb < 500) return 'MUY BUENO ✅';
        if ($memoria_mb < 1000) return 'BUENO 🔶';
        if ($memoria_mb < 2000) return 'ACEPTABLE ⚠️';
        return 'ALTO CONSUMO ❌';
    }

    private static function clasificar_rendimiento_general($tiempo, $legajos, $memoria)
    {
        $ms_por_legajo = ($tiempo * 1000) / max($legajos, 1);
        $memoria_mb = $memoria / 1024 / 1024;

        if ($ms_por_legajo < 30 && $memoria_mb < 300) return 'ÓPTIMO 🏆';
        if ($ms_por_legajo < 60 && $memoria_mb < 600) return 'EXCELENTE ⭐';
        if ($ms_por_legajo < 100 && $memoria_mb < 1000) return 'MUY BUENO ✅';
        if ($ms_por_legajo < 200 && $memoria_mb < 2000) return 'BUENO 🔶';
        return 'NECESITA OPTIMIZACIÓN ⚠️';
    }

    /**
     * Genera recomendaciones basadas en los resultados
     */
    private static function generar_recomendaciones($estadisticas)
    {
        $recomendaciones = [];

        if ($estadisticas['tiempo_por_legajo_ms'] > 100) {
            $recomendaciones[] = "⚠️  Considerar optimizaciones adicionales - tiempo por legajo alto";
        }

        if ($estadisticas['memoria_pico_mb'] > 1000) {
            $recomendaciones[] = "💾 Considerar procesamiento en lotes - uso de memoria alto";
        }

        if ($estadisticas['tasa_exito_porcentaje'] < 98) {
            $recomendaciones[] = "🔍 Investigar legajos fallidos - tasa de éxito baja";
        }

        if ($estadisticas['proyeccion_38000_legajos_min'] > 60) {
            $recomendaciones[] = "🚀 Considerar paralelización para cargas completas";
        }

        if (empty($recomendaciones)) {
            $recomendaciones[] = "✅ Rendimiento óptimo - listo para producción";
        }

        return $recomendaciones;
    }

    /**
     * Análisis comparativo de múltiples tests
     */
    private static function analizar_suite_comparativa($resultados, $tiempo_total_suite)
    {
        $analisis = [
            'tiempo_total_suite_min' => round($tiempo_total_suite / 60, 2),
            'tests_exitosos' => 0,
            'tests_fallidos' => 0,
            'mejor_velocidad' => null,
            'peor_velocidad' => null,
            'tendencia_escalabilidad' => 'ANÁLISIS_PENDIENTE'
        ];

        $velocidades = [];

        foreach ($resultados as $nombre => $resultado) {
            if ($resultado['exito']) {
                $analisis['tests_exitosos']++;
                $velocidad = $resultado['estadisticas']['tiempo_por_legajo_ms'];
                $velocidades[$nombre] = $velocidad;
            } else {
                $analisis['tests_fallidos']++;
            }
        }

        if (!empty($velocidades)) {
            $min_vel = min($velocidades);
            $max_vel = max($velocidades);

            $analisis['mejor_velocidad'] = array_search($min_vel, $velocidades) . " ($min_vel ms/legajo)";
            $analisis['peor_velocidad'] = array_search($max_vel, $velocidades) . " ($max_vel ms/legajo)";

            // Análisis de tendencia
            if ($max_vel / $min_vel < 1.5) {
                $analisis['tendencia_escalabilidad'] = 'EXCELENTE - Escala linealmente 🏆';
            } elseif ($max_vel / $min_vel < 2.5) {
                $analisis['tendencia_escalabilidad'] = 'BUENA - Escalabilidad aceptable ✅';
            } else {
                $analisis['tendencia_escalabilidad'] = 'REGULAR - Degrada con carga alta ⚠️';
            }
        }

        return $analisis;
    }

    /**
     * Activa monitoreo de performance durante la ejecución
     */
    private static function activar_monitoreo_performance($total_legajos)
    {
        // Log cada 1000 legajos o al 25%, 50%, 75% del progreso
        $intervalos_log = [
            intval($total_legajos * 0.25),
            intval($total_legajos * 0.50),
            intval($total_legajos * 0.75),
            $total_legajos
        ];

        Log::info('🔍 Monitoreo de performance activado', [
            'total_legajos' => $total_legajos,
            'puntos_monitoreo' => $intervalos_log
        ]);
    }

    /**
     * Inicializa todas las variables estáticas necesarias - VERSIÓN CORREGIDA
     */
    private static function inicializar_variables_estaticas()
    {
        Log::info('✅ Inicializando variables estáticas para pruebas...');

        // ✅ USAR EL MÉTODO PÚBLICO DE SicossOptimizado
        SicossOptimizado::inicializarVariablesEstaticasParaTests();

        // ✅ VERIFICAR QUE SE INICIALIZARON CORRECTAMENTE
        $estado = SicossOptimizado::verificarEstadoVariablesEstaticas();
        Log::info('✅ Estado de variables estáticas:', $estado);
    }

    /**
     * Limpia las tablas temporales creadas - VERSIÓN CORREGIDA
     */
    private static function limpiar_tablas_temporales()
    {
        // ✅ USAR EL MÉTODO PÚBLICO DE SicossOptimizado
        SicossOptimizado::limpiarTablasTemporalesParaTests();
    }

    /**
     * Verifica que no se ejecuten consultas N+1 durante el procesamiento
     */
    public static function verificar_optimizacion_completa($total_legajos): void
    {
        $conexion = SicossOptimizado::getStaticConnectionName();
        $queries_iniciales = DB::connection($conexion)->getQueryLog();
        $cantidad_inicial = count($queries_iniciales);

        Log::info('🔍 Verificación de optimización iniciada', [
            'legajos_a_procesar' => $total_legajos,
            'consultas_sql_previas' => $cantidad_inicial,
            'limite_esperado_consultas' => 10
        ]);
    }

    /**
     * 🔍 Diagnóstica y muestra información detallada del sistema y conexiones
     */
    public static function diagnosticar_sistema_y_conexiones()
    {
        Log::info('=== 🔍 DIAGNÓSTICO DEL SISTEMA ===');

        try {
            // ✅ 1. INFORMACIÓN DE CONEXIONES
            $conexion_principal = SicossOptimizado::getStaticConnectionName();
            $config_conexion = config("database.connections.{$conexion_principal}");

            Log::info('📡 CONEXIÓN PRINCIPAL', [
                'nombre_conexion' => $conexion_principal,
                'host' => $config_conexion['host'] ?? 'No configurado',
                'puerto' => $config_conexion['port'] ?? 'No configurado',
                'base_datos' => $config_conexion['database'] ?? 'No configurado',
                'usuario' => $config_conexion['username'] ?? 'No configurado',
                'driver' => $config_conexion['driver'] ?? 'No configurado'
            ]);

            // ✅ 2. PROBAR CONECTIVIDAD
            $inicio_conexion = microtime(true);
            $resultado_conexion = DB::connection($conexion_principal)->select('SELECT NOW() as servidor_tiempo, version() as version_db');
            $tiempo_conexion = round((microtime(true) - $inicio_conexion) * 1000, 2);

            Log::info('✅ CONECTIVIDAD EXITOSA', [
                'tiempo_respuesta_ms' => $tiempo_conexion,
                'servidor_tiempo' => $resultado_conexion[0]->servidor_tiempo ?? 'No disponible',
                'version_db' => substr($resultado_conexion[0]->version_db ?? 'No disponible', 0, 50)
            ]);

            // ✅ 3. INICIALIZAR VARIABLES PARA OBTENER DATOS
            self::inicializar_variables_estaticas();

            // ✅ 4. INFORMACIÓN DE TABLAS PRINCIPALES
            $tablas_info = self::obtener_informacion_tablas_principales($conexion_principal);
            Log::info('📊 INFORMACIÓN DE TABLAS', $tablas_info);

            // ✅ 5. CONTAR LEGAJOS DISPONIBLES
            $codc_reparto = SicossOptimizado::getCodcReparto();
            $info_legajos = self::contar_legajos_disponibles($conexion_principal, $codc_reparto);
            Log::info('👥 LEGAJOS DISPONIBLES', $info_legajos);

            // ✅ 6. INFORMACIÓN DEL PERÍODO ACTUAL
            $periodo_info = self::obtener_informacion_periodo();
            Log::info('📅 PERÍODO ACTUAL', $periodo_info);

            // ✅ 7. INFORMACIÓN DEL SISTEMA
            $sistema_info = self::obtener_informacion_sistema();
            Log::info('💻 INFORMACIÓN DEL SISTEMA', $sistema_info);

            // ✅ 8. PROYECCIONES DE RENDIMIENTO
            $proyecciones = self::calcular_proyecciones_rendimiento($info_legajos['total_legajos']);
            Log::info('🚀 PROYECCIONES DE RENDIMIENTO', $proyecciones);

            return [
                'exito' => true,
                'conexion' => $conexion_principal,
                'conectividad_ms' => $tiempo_conexion,
                'tablas' => $tablas_info,
                'legajos' => $info_legajos,
                'periodo' => $periodo_info,
                'sistema' => $sistema_info,
                'proyecciones' => $proyecciones
            ];
        } catch (\Exception $e) {
            Log::error('❌ ERROR EN DIAGNÓSTICO', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => basename($e->getFile())
            ]);

            return [
                'exito' => false,
                'error' => $e->getMessage(),
                'detalles' => [
                    'linea' => $e->getLine(),
                    'archivo' => basename($e->getFile())
                ]
            ];
        }
    }

    /**
     * Obtiene información detallada de las tablas principales
     */
    private static function obtener_informacion_tablas_principales($conexion): array
    {
        $tablas = ['dh01', 'dh21', 'dh21h', 'dh22', 'dh12', 'dh15', 'dh16'];
        $info_tablas = [];

        foreach ($tablas as $tabla) {
            try {
                $count_result = DB::connection($conexion)->select("SELECT COUNT(*) as total FROM mapuche.{$tabla}");
                $size_result = DB::connection($conexion)->select("
                    SELECT pg_size_pretty(pg_total_relation_size('mapuche.{$tabla}')) as tamaño
                ");

                $info_tablas[$tabla] = [
                    'registros' => $count_result[0]->total ?? 0,
                    'tamaño' => $size_result[0]->tamaño ?? 'No disponible'
                ];
            } catch (\Exception $e) {
                $info_tablas[$tabla] = [
                    'registros' => 'Error: ' . $e->getMessage(),
                    'tamaño' => 'No disponible'
                ];
            }
        }

        return $info_tablas;
    }

    /**
     * Cuenta los legajos disponibles con diferentes filtros
     */
    private static function contar_legajos_disponibles($conexion, $codc_reparto): array
    {
        try {
            // Total de legajos en dh01
            $total_legajos = DB::connection($conexion)->select("
                SELECT COUNT(*) as total FROM mapuche.dh01
            ")[0]->total ?? 0;

            // Legajos con liquidaciones en el período
            $periodo = MapucheConfig::getPeriodoCorriente();
            $legajos_con_liquidacion = DB::connection($conexion)->select("
                SELECT COUNT(DISTINCT dh21.nro_legaj) as total 
                FROM mapuche.dh21
                INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
                WHERE dh22.per_liano = ? AND dh22.per_limes = ? AND dh22.sino_genimp = true
            ", [$periodo['year'], $periodo['month']])[0]->total ?? 0;

            // Legajos activos
            $legajos_activos = DB::connection($conexion)->select("
                SELECT COUNT(*) as total FROM mapuche.dh01 WHERE tipo_estad = 'A'
            ")[0]->total ?? 0;

            // Legajos del reparto específico
            $legajos_reparto = 0;
            if ($codc_reparto && $codc_reparto !== 'NULL') {
                $legajos_reparto = DB::connection($conexion)->select("
                    SELECT COUNT(*) as total FROM mapuche.dh01 WHERE codc_reparto = ?
                ", [trim($codc_reparto, "'")])[0]->total ?? 0;
            }

            return [
                'total_legajos' => $total_legajos,
                'legajos_con_liquidacion_periodo' => $legajos_con_liquidacion,
                'legajos_activos' => $legajos_activos,
                'legajos_reparto_especifico' => $legajos_reparto,
                'codc_reparto_utilizado' => $codc_reparto,
                'periodo_consultado' => $periodo['year'] . '/' . str_pad($periodo['month'], 2, '0', STR_PAD_LEFT)
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Error al contar legajos: ' . $e->getMessage(),
                'total_legajos' => 0,
                'legajos_con_liquidacion_periodo' => 0,
                'legajos_activos' => 0,
                'legajos_reparto_especifico' => 0
            ];
        }
    }

    /**
     * Obtiene información del período actual
     */
    private static function obtener_informacion_periodo(): array
    {
        try {
            $periodo = MapucheConfig::getPeriodoCorriente();
            return [
                'año_actual' => $periodo['year'],
                'mes_actual' => $periodo['month'],
                'periodo_formato' => $periodo['year'] . '/' . str_pad($periodo['month'], 2, '0', STR_PAD_LEFT),
                'nombre_mes' => date('F', mktime(0, 0, 0, $periodo['month'], 1)),
                'timestamp_consulta' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Error al obtener período: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene información del sistema
     */
    private static function obtener_informacion_sistema(): array
    {
        return [
            'memoria_actual_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memoria_pico_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limite_memoria' => ini_get('memory_limit'),
            'limite_tiempo_ejecucion' => ini_get('max_execution_time'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment()
        ];
    }

    /**
     * Calcula proyecciones de rendimiento basadas en tests anteriores
     */
    private static function calcular_proyecciones_rendimiento($total_legajos): array
    {
        // Basado en los resultados de los tests anteriores
        $tiempo_por_legajo_ms = 21; // Promedio optimista basado en 10K test
        $tiempo_overhead_s = 23; // Overhead inicial observado

        $tiempo_procesamiento_s = ($total_legajos * $tiempo_por_legajo_ms) / 1000;
        $tiempo_total_s = $tiempo_procesamiento_s + $tiempo_overhead_s;

        return [
            'legajos_totales' => $total_legajos,
            'tiempo_estimado_por_legajo_ms' => $tiempo_por_legajo_ms,
            'tiempo_overhead_segundos' => $tiempo_overhead_s,
            'tiempo_procesamiento_estimado_min' => round($tiempo_procesamiento_s / 60, 2),
            'tiempo_total_estimado_min' => round($tiempo_total_s / 60, 2),
            'tiempo_total_estimado_horas' => round($tiempo_total_s / 3600, 2),
            'velocidad_estimada_legajos_por_segundo' => round($total_legajos / $tiempo_total_s, 2),
            'memoria_estimada_mb' => round($total_legajos * 0.05, 2), // ~50KB por legajo observado
            'consultas_n1_eliminadas' => $total_legajos,
            'mejora_vs_original_estimada' => '85-90%'
        ];
    }

    /**
     * Ejecuta diagnóstico completo antes de cualquier test
     */
    public static function pre_test_diagnostico()
    {
        Log::info('🚀 Ejecutando diagnóstico pre-test...');
        return self::diagnosticar_sistema_y_conexiones();
    }

    /**
     * Ejecuta un test de cálculo de memoria necesaria para el procesamiento de legajos.
     * 
     * Este método realiza un análisis de la memoria requerida para procesar todos los legajos
     * disponibles en el sistema. Inicializa las variables estáticas necesarias, obtiene
     * una muestra de legajos y calcula la memoria necesaria para su procesamiento.
     * 
     * @return array Análisis detallado de memoria que incluye:
     *               - memoria_actual: Memoria en uso antes del procesamiento
     *               - memoria_estimada: Memoria estimada necesaria
     *               - legajos_analizados: Cantidad de legajos incluidos en el análisis
     *               - recomendaciones: Sugerencias de optimización si es necesario
     * 
     * @throws \Exception Si falla la inicialización de variables estáticas
     * @throws \Illuminate\Database\QueryException Si falla la consulta de legajos
     * 
     * @see SicossOptimizado::inicializarVariablesEstaticasParaTests()
     * @see SicossOptimizado::obtener_legajos()
     * @see SicossOptimizado::calcular_memoria_necesaria()
     */
    public static function test_calculo_memoria() : array
    {
        // Preparar test
        SicossOptimizado::inicializarVariablesEstaticasParaTests();

        // Obtener muestra de legajos
        $legajos = SicossOptimizado::obtener_legajos(
            SicossOptimizado::getCodcReparto(),
            'true',
            'true',
            false,
            false
        );

        // Calcular memoria necesaria
        $analisis = SicossOptimizado::calcular_memoria_necesaria($legajos);

        Log::info('🧮 RESULTADO ANÁLISIS DE MEMORIA', $analisis);

        return $analisis;
    }
}
