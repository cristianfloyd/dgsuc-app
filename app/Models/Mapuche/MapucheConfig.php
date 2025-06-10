<?php

namespace App\Models\Mapuche;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\PeriodoFiscalService;

/**
 * Clase de configuración Helper para el sistema Mapuche usando el modelo Rrhhini
 */
class MapucheConfig
{
    use MapucheConnectionTrait;
    /**
     * Obtener el valor de un parámetro de RRHH.
     *
     * @param string $section Sección principal
     * @param string $parameter Nombre del parámetro
     * @param mixed $default Valor por defecto si el parámetro no se encuentra
     * @return mixed
     */
    public static function getParametroRrhh(string $section, string $parameter, $default = null)
    {
        $value = Rrhhini::getParameterValue($section, $parameter);
        return $value ?? $default;
    }

    /**
     * Establecer el valor de un parámetro de RRHH.
     *
     * @param string $section Sección principal
     * @param string $parameter Nombre del parámetro
     * @param mixed $value Valor a establecer
     * @return bool
     */
    public static function setParametroRrhh(string $section, string $parameter, $value): bool
    {
        return Rrhhini::updateOrCreate(
            [
                'nombre_seccion' => $section,
                'nombre_parametro' => $parameter
            ],
            [
                'dato_parametro' => $value
            ]
        )->exists;
    }

    /**
     * Obtener el porcentaje de aporte diferencial de jubilación.
     *
     * Retorna el valor del porcentaje de aporte diferencial de jubilación configurado en el sistema.
     *
     * @return float El valor del porcentaje de aporte diferencial de jubilación.
     */
    public static function getPorcentajeAporteDiferencialJubilacion(): float
    {
        return floatval(self::getParametroRrhh('Porcentaje', 'PorcAporteDiferencialJubilacion', 0));
    }

    /**
     * Obtener el valor de informar becarios para SICOSS.
     *
     * Retorna el valor configurado para informar becarios en SICOSS.
     *
     * @return bool El valor de informar becarios.
     */
    public static function getSicossInformarBecarios(): bool
    {
        return (bool)self::getParametroRrhh('Sicoss', 'InformarBecario', 0);
    }

    /**
     * Obtener el valor de ART con tope.
     *
     * Retorna el valor configurado para ART con tope.
     *
     * @return string El valor de ART con tope.
     */
    public static function getSicossArtTope(): string
    {
        return self::getParametroRrhh('Sicoss', 'ARTconTope', '1');
    }

    /**
     * Obtener los conceptos no remunerativos incluidos en el ART.
     *
     * Retorna el valor de los conceptos no remunerativos incluidos en el ART, configurados en el sistema.
     *
     * @return string El valor de los conceptos no remunerativos incluidos en el ART.
     */
    public static function getSicossConceptosNoRemunerativosEnArt(): string
    {
        return self::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0');
    }

    /**
     * Obtener las categorías de aportes diferenciales configuradas.
     *
     * Retorna el valor de las categorías de aportes diferenciales configuradas en el sistema.
     *
     * @return string El valor de las categorías de aportes diferenciales.
     */
    public static function getSicossCategoriasAportesDiferenciales(): string
    {
        return self::getParametroRrhh('Sicoss', 'CategoriasAportesDiferenciales', '0');
    }

    /**
     * Obtener el número de horas extras para novedades.
     *
     * Retorna el número de horas extras configuradas para novedades.
     *
     * @return int El número de horas extras.
     */
    public static function getSicossHorasExtrasNovedades(): int
    {
        return (int)self::getParametroRrhh('Sicoss', 'HorasExtrasNovedades', 0);
    }

    /**
     * Obtener los parámetros de ajustes de imputaciones contables.
     *
     * Retorna el valor del parámetro de ajustes de imputaciones contables, que indica si está habilitado o deshabilitado.
     *
     * @return string El valor del parámetro, que puede ser 'Habilitada' o 'Deshabilitada'.
     */
    public static function getParametrosAjustesImpContable(): string
	{
		return self::getParametroRrhh('Presupuesto', 'GestionAjustesImputacionesPresupuestarias','Deshabilitada');
	}

    /**
     * Obtener el año fiscal actual.
     *
     * @return string
     */
    public static function getAnioFiscal(): string
    {
        $periodoService = app(PeriodoFiscalService::class);
        return $periodoService->getYear();
    }

    /**
     * Obtener el mes fiscal actual.
     *
     * @return string
     */
    public static function getMesFiscal(): string
    {
        $periodoService = app(PeriodoFiscalService::class);
        return $periodoService->getMonth();
    }

    /**
     * Obtener el periodo fiscal actual.
     *
     * Concatena el año y mes fiscal actual en formato YYYYMM.
     *
     * @return string El periodo fiscal en formato YYYYMM (ej: '202304')
     */
    public static function getPeriodoFiscal(): string
    {
        $periodoService = app(PeriodoFiscalService::class);

        return $periodoService->getYear() . $periodoService->getMonth();
    }

    public static function getPeriodoCorriente()
	{
        $periodoService = app(PeriodoFiscalService::class);

		return $periodoService->getPeriodoFiscal();
	}

    /**
     * Obtener la fecha de inicio del periodo fiscal corriente.
     *
     * @return string La fecha en formato Y-m-d
     */
    public static function getFechaInicioPeriodoCorriente(): string
    {
        return DB::connection(self::getStaticConnectionName())
        ->select('SELECT map_get_fecha_inicio_periodo() as fecha_inicio')[0]->fecha_inicio;
    }

    /**
     * Obtener la fecha de fin del periodo fiscal corriente.
     *
     * @return string La fecha en formato Y-m-d
     */
    public static function getFechaFinPeriodoCorriente(): string
    {
        return DB::connection(self::getStaticConnectionName())
        ->select('SELECT map_get_fecha_fin_periodo() as fecha_fin')[0]->fecha_fin;
    }

    /**
     * Obtener las variantes de licencias de 10 días.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicencias10Dias(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesILTPrimerTramo', '');
    }

    /**
     * Obtener las variantes de licencias de 11 días siguientes.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicencias11DiasSiguientes(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesILTSegundoTramo', '');
    }

    /**
     * Obtener las variantes de licencias de maternidad down.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicenciasMaternidadDown(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesMaternidadDown', '');
    }

    /**
     * Obtener las variantes de licencias de excedencia.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicenciaExcedencia(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesExcedencia', '');
    }

    /**
     * Obtener las variantes de licencias de vacaciones.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicenciaVacaciones(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesVacaciones', '');
    }

    /**
     * Obtener las variantes de licencias de protección integral.
     *
     * @return string Lista de IDs separados por comas
     */
    public static function getVarLicenciaProtecIntegral(): string
    {
        return self::getParametroRrhh('Licencias', 'VariantesProtecIntegral', '');
    }

    public static function getCategoriasDiferencial(): string
    {
		return self::getParametroRrhh('Sicoss', 'CategoriaDiferencial');
	}



    /**
     * Obtiene el nombre de la conexión de base de datos de forma estática.
     *
     * Este método crea una instancia de la clase actual y utiliza el trait MapucheConnectionTrait
     * para obtener el nombre de la conexión configurada.
     *
     * @return string El nombre de la conexión de base de datos configurada
     */
    public static function getStaticConnectionName(): string
    {
        $instance = new static();
        return $instance->getConnectionName();
    }

    public static function getDefaultsObraSocial()
	{
		return self::getParametroRrhh('Defaults', 'ObraSocial');
	}

    public static function getConceptosObraSocialAporteAdicional()
	{
		return self::getParametroRrhh('Conceptos', 'ObraSocialAporteAdicional');
	}

    public static function getConceptosObraSocialAporte()
	{
		return self::getParametroRrhh('Conceptos', 'ObraSocialAporte');
	}

    public static function getConceptosObraSocialRetro()
	{
		return self::getParametroRrhh('Conceptos', 'ObraSocialRetro');
	}

    public static function getConceptosObraSocial()
	{
		return self::getParametroRrhh('Conceptos', 'ObraSocial');
	}



    public static function getTopesJubilacionVoluntario()
	{
		return self::getParametroRrhh('Conceptos', 'JubilacionVoluntario');
	}


    public static function getTopesJubilatorioPatronal()
	{
		return self::getParametroRrhh('Topes', 'TopeJubilatorioPatronal');
	}

    public static function getTopesJubilatorioPersonal()
	{
		return self::getParametroRrhh('Topes', 'TopeJubilatorioPersonal');
	}

    public static function getTopesOtrosAportesPersonales()
	{
		return self::getParametroRrhh('Topes', 'TopeOtrosAportesPersonales');
	}

    public static function getConceptosObraSocialFliarAdherente()
	{
		return self::getParametroRrhh('Conceptos', 'ObraSocialFliarAdherente');
	}

    public static function getDatosUniversidadCuit()
	{
		return self::getParametroRrhh('Datos Universidad', 'CUIT');
	}

    public static function getDatosUniversidadDireccion()
	{
		return self::getParametroRrhh('Datos Universidad', 'Direccion');
	}

    public static function getDatosCodcReparto()
	{
		return self::getParametroRrhh('Datos Universidad', 'Cod.R�gimen de Reparto');
	}

    public static function getDatosUniversidadCiudad()
	{
		return self::getParametroRrhh('Datos Universidad', 'Ciudad');
	}

    public static function getDatosUniversidadSigla() {
		return self::getParametroRrhh('Datos Universidad', 'Sigla');
	}

    public static function getDatosUniversidadTipoEmpresa() {
		return self::getParametroRrhh('Datos Universidad', 'TipoEmpresa');
	}

    public static function getDatosUniversidadTrabajadorConvencionado() {
		return self::getParametroRrhh('Datos Universidad', 'TrabajadorConvencionado');
	}

    public static function getConceptosInformarAdherentesSicoss()
	{
		return self::getParametroRrhh('Conceptos', 'AdherenteSicossDesdeH09',0);
	}
    public static function getConceptosAcumularAsigFamiliar()
	{
		return self::getParametroRrhh('Conceptos', 'AcumularAsigFamiliar',1);
	}
}
