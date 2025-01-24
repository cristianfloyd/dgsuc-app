<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;

/**
 * Este servicio maneja la creación y eliminación de la tabla (temporal) de NovedadesCargoImport
 * en PostgreSQL, con el propósito de que sus datos sean efímeros y se destruyan
 * al iniciar o cerrar la aplicación (o al cerrarse la conexión).
 *
 * Se ejemplifica usando una TEMP TABLE con ON COMMIT DROP o ON COMMIT DELETE ROWS.
 * - "ON COMMIT DROP" destruye toda la estructura y sus datos al terminar la transacción/sesión.
 * - "ON COMMIT DELETE ROWS" mantiene el esquema pero elimina filas al finalizar la transacción.
 *
 * Dependiendo de la necesidad, se puede escoger la instrucción apropiada para el caso.
 */
class NovedadesCargoImportTableService
{
    use MapucheConnectionTrait;


    // -------------------------------------------------------------------------
    // Nombre de la tabla que se usará como temporal. Se aconseja un nombre único
    // para evitar conflictos con otras tablas temporales.
    // -------------------------------------------------------------------------
    protected string $TableName = 'suc.novedades_cargo_imports';

    // -------------------------------------------------------------------------
    // Creación de la tabla temporal en PostgreSQL
    // -------------------------------------------------------------------------
    public function createTable(): void
    {
        try {
            // IMPORTANTE:
            // - CREATE TEMP TABLE: la tabla vive solo durante la sesión actual.
            // - IF NOT EXISTS no funciona con tablas temporales en psql antiguas,
            //   por lo que se manejan excepciones en caso de ya existir.
            // - ON COMMIT DROP indica que la tabla se eliminará al cierre de la transacción.
            //   ON COMMIT DELETE ROWS elimina datos, pero mantiene la estructura.
            //   Ajustar según preferencia.

            $sql = "
            CREATE TABLE {$this->TableName} (
                id                        SERIAL PRIMARY KEY,
                codigoNovedad            VARCHAR(9),
                numLegajo                VARCHAR(9),
                numCargo                 VARCHAR(10),
                tipoNovedad             VARCHAR(1),
                yearVigencia             INT,
                monthVigencia            INT,
                numeroLiquidacion        INT,
                codigoConcepto           VARCHAR(3),
                novedad1                 VARCHAR(10),
                novedad2                 VARCHAR(10),
                condicionNovedad         VARCHAR(1),
                yearFinalizacion         INT,
                monthFinalizacion        INT,
                yearRetro                INT,
                monthRetro               INT,
                yearComienzo            INT,
                monthComienzo           INT,
                detalleNovedad           VARCHAR(10),
                anulaNovedadMultiple     VARCHAR(1),
                tipoEjercicio            VARCHAR(1),
                grupoPresupuestario      INT,
                unidadPrincipal          INT,
                unidadSubPrincipal       INT,
                unidadSubSubPrincipal    INT,
                fuenteFondos             INT,
                programa                 INT,
                subPrograma              INT,
                proyecto                 INT,
                actividad                INT,
                obra                     INT,
                finalidad                INT,
                funcion                  INT,
                tenerCtaEjercicio        VARCHAR(1),
                tenerCtaGrupoPresup      VARCHAR(1),
                tenerCtaUnidadPrincipal  VARCHAR(1),
                tenerCtaFuente           VARCHAR(1),
                tenerCtaRedProgramatica  VARCHAR(1),
                tenerCtaFinalidadFuncion VARCHAR(1),
                conActualizacion         BOOLEAN,
                nuevosIdentificadores    BOOLEAN,
                errors                   JSONB
            )
            ";

            DB::connection($this->getConnectionName())->statement($sql);
        } catch (\Throwable $th) {
            // Si la tabla ya existe o hay otro problema, lo registramos:
            Log::error('Error al crear la tabla temporal: '.$th->getMessage());
            throw $th;
        }
    }

    // -------------------------------------------------------------------------
    // Eliminación manual de la tabla temporal
    // -------------------------------------------------------------------------
    public function dropTempTable(): void
    {
        try {
            // Al usar ON COMMIT DROP, la tabla se elimina sola al terminar la sesión,
            // pero en caso de querer forzar su eliminación antes, se usa DROP TABLE:
            $sql = "DROP TABLE IF EXISTS {$this->TableName};";
            DB::connection($this->getConnectionName())->statement($sql);

        } catch (\Throwable $th) {
            // Registramos excepciones y volvemos a lanzar
            Log::error('Error al eliminar la tabla temporal: '.$th->getMessage());
            throw $th;
        }
    }

    // -------------------------------------------------------------------------
    // Método de ejemplo para insertar datos de prueba,
    // mostrando cómo se utiliza la tabla temporal en la misma sesión.
    // -------------------------------------------------------------------------
    public function insertTempData(array $data): void
    {
        try {
            DB::connection($this->getConnectionName())->table($this->TableName)->insert($data);
        } catch (\Throwable $th) {
            Log::error('Error al insertar datos en la tabla temporal: '.$th->getMessage());
            throw $th;
        }
    }

    // -------------------------------------------------------------------------
    // Ejemplo de obtención de datos desde la tabla temporal
    // -------------------------------------------------------------------------
    public function getTempData(): array
    {
        try {
            return DB::connection($this->getConnectionName())->table($this->TableName)->get()->toArray();
        } catch (\Throwable $th) {
            Log::error('Error al leer datos de la tabla temporal: '.$th->getMessage());
            throw $th;
        }
    }
}
