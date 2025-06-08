--
-- Name: dl02; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dl02 (
    nrovarlicencia integer NOT NULL,
    nrodefiniclicencia integer,
    codn_tipo_lic character(4),
    es_remunerada boolean,
    porcremuneracion double precision,
    seaplicaa character(1),
    escalafon character(1),
    seapacualcaracter boolean,
    seapacualdedic boolean,
    sexo character(1),
    control_fechas boolean,
    unidad_tiempo character(1),
    duracionenunidades integer,
    tipo_dias character(1),
    cantfragmentosmax integer,
    min_dias_fragmento integer,
    max_dias_fragmento integer,
    periodicidad character(1),
    cantunidadesperiod integer,
    unidadtiempoantig character(1),
    antigdesdeenunidad integer,
    antighastaenunidad integer,
    nroordenaplicacion integer,
    computa_antiguedad boolean,
    computa_antig_ordi boolean,
    es_absorcion boolean,
    es_maternidad boolean,
    genera_vacante boolean,
    libera_horas boolean,
    libera_puntos integer,
    observacion character varying(255),
    subcontrol_fechas boolean,
    subcantfragmentosmax integer,
    submin_dias_fragmento integer,
    submax_dias_fragmento integer,
    subperiodicidad character(1),
    subcantunidadesperiod integer,
    subduracionenunidades integer,
    seapacualcateg integer,
    chkpresentismo integer,
    chkaportalao integer,
    cantunidadesperiodo_sinusar integer
);


--
-- Name: TABLE dl02; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dl02 IS '(D) Variantes de Licencias';


--
-- Name: COLUMN dl02.nrovarlicencia; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.nrovarlicencia IS 'Número de variante de licencia';


--
-- Name: COLUMN dl02.nrodefiniclicencia; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.nrodefiniclicencia IS 'Número de definición de licencia';


--
-- Name: COLUMN dl02.codn_tipo_lic; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.codn_tipo_lic IS 'Nombre del tipo de licencia';


--
-- Name: COLUMN dl02.es_remunerada; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.es_remunerada IS 'Licencia es remunerada? [S/N]';


--
-- Name: COLUMN dl02.porcremuneracion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.porcremuneracion IS 'Porcentaje de temuneración percibido';


--
-- Name: COLUMN dl02.seaplicaa; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.seaplicaa IS 'Aplica a personas o cargos';


--
-- Name: COLUMN dl02.escalafon; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.escalafon IS 'Escalafón válido para la licencia';


--
-- Name: COLUMN dl02.seapacualcaracter; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.seapacualcaracter IS 'Aplica a cualquier carácter?[S/N]';


--
-- Name: COLUMN dl02.seapacualdedic; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.seapacualdedic IS 'Es para cualquier dedicación? [S/N]';


--
-- Name: COLUMN dl02.sexo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.sexo IS 'Sexo válido para la licencia';


--
-- Name: COLUMN dl02.control_fechas; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.control_fechas IS 'Existe control de fechas?[S/N]';


--
-- Name: COLUMN dl02.unidad_tiempo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.unidad_tiempo IS 'Unidad de tiempo (Día, Mes, Año)';


--
-- Name: COLUMN dl02.duracionenunidades; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.duracionenunidades IS 'Duración unidades de tiempo';


--
-- Name: COLUMN dl02.tipo_dias; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.tipo_dias IS 'Días corridos o hábiles';


--
-- Name: COLUMN dl02.cantfragmentosmax; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.cantfragmentosmax IS 'Cantidad máxima de fragmentos';


--
-- Name: COLUMN dl02.min_dias_fragmento; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.min_dias_fragmento IS 'Mínima cantidad de días por fragmento';


--
-- Name: COLUMN dl02.max_dias_fragmento; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.max_dias_fragmento IS 'Máxima cantidad de días por fragmento';


--
-- Name: COLUMN dl02.periodicidad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.periodicidad IS 'Tipo de periodicidad';


--
-- Name: COLUMN dl02.cantunidadesperiod; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.cantunidadesperiod IS 'Cantidad de unidades para la periodicidad';


--
-- Name: COLUMN dl02.unidadtiempoantig; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.unidadtiempoantig IS 'Unidad de tiempo (Mes, Año)';


--
-- Name: COLUMN dl02.antigdesdeenunidad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.antigdesdeenunidad IS 'Límite inferior de antigüedad para variante';


--
-- Name: COLUMN dl02.antighastaenunidad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.antighastaenunidad IS 'Límite superior de antigüedad para variante';


--
-- Name: COLUMN dl02.nroordenaplicacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.nroordenaplicacion IS 'Número de orden de aplicación';


--
-- Name: COLUMN dl02.computa_antiguedad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.computa_antiguedad IS 'Computa antigüedad remunerada? [S/N]';


--
-- Name: COLUMN dl02.computa_antig_ordi; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.computa_antig_ordi IS 'Computa antigüedad ordinaria? [S/N]';


--
-- Name: COLUMN dl02.es_absorcion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.es_absorcion IS 'Es una licencia por absorción';


--
-- Name: COLUMN dl02.es_maternidad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.es_maternidad IS 'Licencia por maternidad? [S/N]';


--
-- Name: COLUMN dl02.genera_vacante; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.genera_vacante IS 'Genera una vacante? [S/N]';


--
-- Name: COLUMN dl02.libera_horas; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.libera_horas IS 'Libera horas para control por horas';


--
-- Name: COLUMN dl02.libera_puntos; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.libera_puntos IS 'Libera puntos para control por horas';


--
-- Name: COLUMN dl02.observacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.observacion IS 'Observación para la variante';


--
-- Name: COLUMN dl02.subcontrol_fechas; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.subcontrol_fechas IS 'Subcontrol de fechas? [S/N]';


--
-- Name: COLUMN dl02.subcantfragmentosmax; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.subcantfragmentosmax IS 'Máxima cantidad de fragmentos subperíodo';


--
-- Name: COLUMN dl02.submin_dias_fragmento; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.submin_dias_fragmento IS 'Mín. cant. días c/fragmento - subperíodo';


--
-- Name: COLUMN dl02.submax_dias_fragmento; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.submax_dias_fragmento IS 'Máx. cant. días c/fragmento - subperíodo';


--
-- Name: COLUMN dl02.subperiodicidad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.subperiodicidad IS 'Tipo de periodicidad para subperiodo';


--
-- Name: COLUMN dl02.subcantunidadesperiod; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.subcantunidadesperiod IS 'Cant. de unidades para periodicidad - subperíodo';


--
-- Name: COLUMN dl02.subduracionenunidades; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.subduracionenunidades IS 'Duración de unidades del subperíodo';


--
-- Name: COLUMN dl02.seapacualcateg; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.seapacualcateg IS 'Cualquier categoría? [S/N]';


--
-- Name: COLUMN dl02.chkpresentismo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.chkpresentismo IS 'Admite presentismo? [S/N]';


--
-- Name: COLUMN dl02.chkaportalao; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dl02.chkaportalao IS 'Aporta meses trabajados? [S/N]';


--
-- Name: COLUMN dl02.cantunidadesperiodo_sinusar; Type: COMMENT; Schema: mapuche; Owner: -
--
