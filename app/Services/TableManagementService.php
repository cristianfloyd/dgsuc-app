<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use App\Contracts\TableManagementServiceInterface;

class TableManagementService implements TableManagementServiceInterface
{
    use MapucheConnectionTrait;
    private static $connectionInstance = null;


    protected static function getMapucheConnection(): Connection
    {
        if (self::$connectionInstance === null) {
            $model = new static;
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }

    protected static function getMapucheConnectionName(): string
    {
        return (new static)->getConnectionFromTrait()->getName();
    }




    /**
     * Verifica y prepara una tabla de base de datos.
     *
     * Este método se encarga de verificar si una tabla existe en la base de datos y, si no existe, la crea. Si la tabla ya existe y contiene datos, los elimina.
     *
     * @param string $tableName El nombre de la tabla a verificar y preparar.
     * @param string|null $connection El nombre de la conexión de base de datos a utilizar. Si se omite, se utilizará la conexión predeterminada.
     * @return array Un arreglo que contiene información sobre el estado de la tabla, incluyendo si se creó, truncó o simplemente se verificó.
     */
    public static function verifyAndPrepareTable(string $tableName, string $connection = null): array
    {
        try {
            // Separar schema y nombre de tabla si se proporciona con formato schema.tabla
            $parts = explode('.', $tableName);
            $schema = count($parts) > 1 ? $parts[0] : 'suc';  // Default schema 'suc'
            $tableNameWithoutSchema = count($parts) > 1 ? $parts[1] : $parts[0];

            // Obtenemos el nombre de la conexión como string
            $connectionName = $connection ?: self::getMapucheConnectionName();

            $schemaBuilder = self::getSchemaConnection($connectionName);
            $db = self::getDbConnection($connectionName);

            $result = [
                'success' => true,
                'message' => "Tabla {$schema}.{$tableNameWithoutSchema} verificada y preparada.",
                'actions' => [],
                'data' => [
                    'schema' => $schema,
                    'tableName' => $tableNameWithoutSchema,
                    'fullTableName' => "{$schema}.{$tableNameWithoutSchema}",
                    'connection' => $connectionName
                ]
            ];

            // Verificar existencia de la tabla en el schema correcto
            if (!$schemaBuilder->hasTable("{$schema}.{$tableNameWithoutSchema}")) {
                static::createTable($tableNameWithoutSchema, $schemaBuilder);
                $result['actions'][] = 'created';
            } elseif (static::tableHasData("{$schema}.{$tableNameWithoutSchema}", $db)) {
                static::truncateTable("{$schema}.{$tableNameWithoutSchema}", $db);
                $result['actions'][] = 'truncated';
            } else {
                $result['actions'][] = 'verified';
            }

            Log::info($result['message'], $result['actions']);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al verificar y preparar la tabla {$tableName}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error al verificar y preparar la tabla {$tableName}",
                'error' => $e->getMessage(),
                'data' => [
                    'schema' => $schema ?? null,
                    'tableName' => $tableNameWithoutSchema ?? null,
                    'connection' => $connectionName ?? null
                ]
            ];
        }
    }



    /**
     * Obtiene una instancia de la conexión de base de datos especificada.
     *
     * Si se proporciona una conexión, se utiliza esa conexión. De lo contrario, se utiliza la conexión predeterminada configurada en la aplicación.
     *
     * @param string|null $connection El nombre de la conexión de base de datos a utilizar.
     * @return \Illuminate\Database\Schema\Builder La instancia de la conexión de base de datos.
     */
    private static function getSchemaConnection(?string $connectionName = null)
    {
        return Schema::connection($connectionName ?: self::getMapucheConnectionName());
    }

    /**
     * Obtiene una instancia de la conexión de base de datos especificada.
     *
     * Si se proporciona una conexión, se utiliza esa conexión. De lo contrario, se utiliza la conexión predeterminada configurada en la aplicación.
     *
     * @param string|null $connection El nombre de la conexión de base de datos a utilizar.
     * @return \Illuminate\Database\Connection La instancia de la conexión de base de datos.
     */
    private static function getDbConnection(?string $connectionName = null)
    {
        return DB::connection($connectionName ?: self::getMapucheConnectionName());
    }

    private static function createTable(string $tableName, $schema): void
    {
        if ($tableName === 'afip_mapuche_sicoss') {
            static::createTableMapucheSicoss($tableName, $schema);
        } elseif ($tableName === 'afip_relaciones_activas') {
            static::createTableRelacionesActivas($tableName, $schema);
        }
    }


    /**
     * Crea la tabla 'afip_mapuche_sicoss' con un esquema de base de datos específico.
     *
     * Este método se encarga de crear la tabla 'afip_mapuche_sicoss' con el esquema de base de datos proporcionado.
     *
     * @param string $tableName El nombre de la tabla a crear.
     * @param $schema El esquema de base de datos a utilizar para crear la tabla.
     */
    private static function createTableMapucheSicoss(string $tableName, $schema): void
    {
        $schema->create($tableName, function (Blueprint $table) {
            $table->char('periodo_fiscal', 6);
            $table->char('cuil', 11)->nullable()->unique();
            $table->char('apnom', 30)->nullable();
            $table->char('conyuge', 1)->nullable();
            $table->char('cant_hijos', 2)->nullable();
            $table->char('cod_situacion', 2)->nullable();
            $table->char('cod_cond', 2)->nullable();
            $table->char('cod_act', 3)->nullable();
            $table->char('cod_zona', 2)->nullable();
            $table->char('porc_aporte', 5)->nullable();
            $table->char('cod_mod_cont', 3)->nullable();
            $table->char('cod_os', 6)->nullable();
            $table->char('cant_adh', 2)->nullable();
            $table->char('rem_total', 12)->nullable();
            $table->char('rem_impo1', 12)->nullable();
            $table->char('asig_fam_pag', 9)->nullable();
            $table->char('aporte_vol', 9)->nullable();
            $table->char('imp_adic_os', 9)->nullable();
            $table->char('exc_aport_ss', 9)->nullable();
            $table->char('exc_aport_os', 9)->nullable();
            $table->char('prov', 50)->nullable();
            $table->char('rem_impo2', 12)->nullable();
            $table->char('rem_impo3', 12)->nullable();
            $table->char('rem_impo4', 12)->nullable();
            $table->char('cod_siniestrado', 2)->nullable();
            $table->char('marca_reduccion', 1)->nullable();
            $table->char('recomp_lrt', 9)->nullable();
            $table->char('tipo_empresa', 1)->nullable();
            $table->char('aporte_adic_os', 9)->nullable();
            $table->char('regimen', 1)->nullable();
            $table->char('sit_rev1', 2)->nullable();
            $table->char('dia_ini_sit_rev1', 2)->nullable();
            $table->char('sit_rev2', 2)->nullable();
            $table->char('dia_ini_sit_rev2', 2)->nullable();
            $table->char('sit_rev3', 2)->nullable();
            $table->char('dia_ini_sit_rev3', 2)->nullable();
            $table->char('sueldo_adicc', 12)->nullable();
            $table->char('sac', 12)->nullable();
            $table->char('horas_extras', 12)->nullable();
            $table->char('zona_desfav', 12)->nullable();
            $table->char('vacaciones', 12)->nullable();
            $table->char('cant_dias_trab', 9)->nullable();
            $table->char('rem_impo5', 12)->nullable();
            $table->char('convencionado', 1)->nullable();
            $table->char('rem_impo6', 12)->nullable();
            $table->char('tipo_oper', 1)->nullable();
            $table->char('adicionales', 12)->nullable();
            $table->char('premios', 12)->nullable();
            $table->char('rem_dec_788_05', 12)->nullable();
            $table->char('rem_imp7', 12)->nullable();
            $table->char('nro_horas_ext', 3)->nullable();
            $table->char('cpto_no_remun', 12)->nullable();
            $table->char('maternidad', 12)->nullable();
            $table->char('rectificacion_remun', 9)->nullable();
            $table->char('rem_imp9', 12)->nullable();
            $table->char('contrib_dif', 9)->nullable();
            $table->char('hstrab', 3)->nullable();
            $table->char('seguro', 1)->nullable();
            $table->char('ley_27430', 12)->nullable();
            $table->char('incsalarial', 12)->nullable();
            $table->char('remimp11', 12)->nullable();

            // Definir la clave primaria compuesta.
            $table->primary(['periodo_fiscal', 'cuil']);
            // $table->foreign('cuil')->references('cuil')->on('mapuche.dh01');
        });


        Log::info("Tabla $tableName creada en la conexión {$schema->getConnection()->getName()} usando la migración existente.");
    }


    private static function createTableRelacionesActivas(string $tableName, $schema): void
    {
        $schema->create($tableName, function (Blueprint $table) {
            // $table->id();
            $table->char('periodo_fiscal', 6);
            $table->char('codigo_movimiento', 2)->nullable();
            $table->char('tipo_registro', 2)->nullable();
            $table->char('cuil', 11)->index();
            $table->char('marca_trabajador_agropecuario', 1)->nullable();
            $table->char('modalidad_contrato', 3)->nullable();
            $table->char('fecha_inicio_relacion_laboral', 10);
            $table->char('fecha_fin_relacion_laboral', 10)->nullable();
            $table->char('codigo_o_social', 6)->nullable();
            $table->char('cod_situacion_baja', 2)->nullable();
            $table->char('fecha_telegrama_renuncia', 10)->nullable();
            $table->char('retribucion_pactada', 15);
            $table->char('modalidad_liquidacion', 1);
            $table->char('suc_domicilio_desem', 5)->nullable();
            $table->char('actividad_domicilio_desem', 6)->nullable();
            $table->char('puesto_desem', 4)->nullable();
            $table->char('rectificacion', 1)->nullable();
            $table->char('numero_formulario_agro', 10)->nullable();
            $table->char('tipo_servicio', 3)->nullable();
            $table->char('categoria_profesional', 6)->nullable();
            $table->char('ccct', 7)->nullable();
            $table->char('no_hay_datos', 4)->nullable();
        });
        Log::info("Tabla $tableName creada en la conexión {$schema->getConnection()->getName()} usando la migración existente.");
    }

    /**
     * Verifica si una tabla de la base de datos tiene datos.
     *
     * @param string $tableName Nombre de la tabla a verificar.
     * @param \Illuminate\Database\ConnectionInterface $db Conexión a la base de datos.
     * @return bool Verdadero si la tabla tiene datos, falso de lo contrario.
     */
    private static function tableHasData(string $tableName, $db): bool
    {
        $count = $db->table($tableName)->count();
        return $count > 0;
    }

    /**
     * Trunca una tabla de la base de datos.
     *
     * @param string $tableName Nombre de la tabla a truncar.
     * @param \Illuminate\Database\ConnectionInterface $db Conexión a la base de datos.
     * @return void
     */
    private static function truncateTable(string $tableName, $db): void
    {
        $db->statement("TRUNCATE TABLE {$tableName} RESTART IDENTITY CASCADE");
        Log::info("Tabla $tableName truncada y secuencias reiniciadas.");
    }

    /**
     * Verifica si una tabla está vacía.
     *
     * @param \Illuminate\Database\Eloquent\Model $model Modelo Eloquent de la tabla a verificar.
     * @param string $tableName Nombre de la tabla a verificar.
     * @return bool Verdadero si la tabla está vacía, falso de lo contrario.
     */
    public static function verifyTableIsEmpty(Model $model, string $tableName): bool
    {
        $tableIsEmpty = $model->all()->isEmpty();

        if ($tableIsEmpty) {
            return false;
        } else {
            return true;
        }
    }
}
