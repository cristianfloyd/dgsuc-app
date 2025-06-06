--
-- Name: dh09; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh09 (
    nro_legaj integer NOT NULL,
    vig_otano integer,
    vig_otmes integer,
    nro_tab02 integer,
    codc_estcv character(4),
    sino_embargo boolean,
    sino_otsal character(1),
    sino_jubil character(1),
    nro_tab08 integer,
    codc_bprev character(4),
    nro_tab09 integer,
    codc_obsoc character(4),
    nro_afili character(15),
    fec_altos date,
    fec_endjp date,
    desc_envio character(20),
    cant_cargo integer,
    desc_tarea character varying(40),
    codc_regio character(4),
    codc_uacad character(4),
    fec_vtosf date,
    fec_reasf date,
    fec_defun date,
    fecha_jubilacion date,
    fecha_grado date,
    nro_agremiacion integer,
    fecha_permanencia date,
    ua_asigfamiliar character(4),
    fechadjur894 date,
    renunciadj894 character(1),
    fechadechere date,
    coddependesemp character(4),
    conyugedependiente integer,
    fec_ingreso date,
    codc_uacad_seguro character(4),
    fecha_recibo date,
    tipo_norma character(20),
    nro_norma integer,
    tipo_emite character(20),
    fec_norma date,
    fuerza_reparto boolean DEFAULT false NOT NULL
);


--
-- Name: TABLE dh09; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh09 IS '(D) Otros Datos del Empleado';


--
-- Name: COLUMN dh09.nro_legaj; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_legaj IS 'Número de legajo';


--
-- Name: COLUMN dh09.vig_otano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.vig_otano IS 'Año de vigencia de otros datos';


--
-- Name: COLUMN dh09.vig_otmes; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.vig_otmes IS 'Mes de vigencia de otros datos';


--
-- Name: COLUMN dh09.nro_tab02; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_tab02 IS 'Número de Tabla Múltiple';


--
-- Name: COLUMN dh09.codc_estcv; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_estcv IS 'Código estado civil ';


--
-- Name: COLUMN dh09.sino_embargo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.sino_embargo IS 'Permite neto menor que salario familiar [S/N]';


--
-- Name: COLUMN dh09.sino_otsal; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.sino_otsal IS 'Salario familiar en otro organismo [S/N]';


--
-- Name: COLUMN dh09.sino_jubil; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.sino_jubil IS 'Jubilado [S/N]';


--
-- Name: COLUMN dh09.nro_tab08; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_tab08 IS 'Referencia a dh30';


--
-- Name: COLUMN dh09.codc_bprev; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_bprev IS 'Tipo de beneficio previsional';


--
-- Name: COLUMN dh09.nro_tab09; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_tab09 IS 'Referencia a dh30';


--
-- Name: COLUMN dh09.codc_obsoc; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_obsoc IS 'Código obra social';


--
-- Name: COLUMN dh09.nro_afili; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_afili IS 'Número afiliado';


--
-- Name: COLUMN dh09.fec_altos; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_altos IS 'Fecha de alta obra social';


--
-- Name: COLUMN dh09.fec_endjp; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_endjp IS 'Fecha de envío de la declaración jurada';


--
-- Name: COLUMN dh09.desc_envio; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.desc_envio IS 'Descripción envío de la declaración jurada';


--
-- Name: COLUMN dh09.cant_cargo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.cant_cargo IS 'Cantidad familiares a cargo';


--
-- Name: COLUMN dh09.desc_tarea; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.desc_tarea IS 'Descripción tarea';


--
-- Name: COLUMN dh09.codc_regio; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_regio IS 'Regional de la dependencia de cabecera';


--
-- Name: COLUMN dh09.codc_uacad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_uacad IS 'Dependencia de cabecera';


--
-- Name: COLUMN dh09.fec_vtosf; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_vtosf IS 'Fecha vencimiento aptitud psicofísica';


--
-- Name: COLUMN dh09.fec_reasf; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_reasf IS 'Fecha realización aptitud psicofísica';


--
-- Name: COLUMN dh09.fec_defun; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_defun IS 'Fecha de defunción';


--
-- Name: COLUMN dh09.fecha_jubilacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fecha_jubilacion IS 'Fecha de jubilación';


--
-- Name: COLUMN dh09.fecha_grado; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fecha_grado IS 'Fecha de grado';


--
-- Name: COLUMN dh09.nro_agremiacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_agremiacion IS 'Número de agremiación';


--
-- Name: COLUMN dh09.fecha_permanencia; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fecha_permanencia IS 'Fecha de permanencia';


--
-- Name: COLUMN dh09.ua_asigfamiliar; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.ua_asigfamiliar IS ' Dependencia asignaciones familiares';


--
-- Name: COLUMN dh09.fechadjur894; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fechadjur894 IS 'Fecha declaración jurada decreto 894/01';


--
-- Name: COLUMN dh09.renunciadj894; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.renunciadj894 IS 'Cargo (C), Jubilación (J)';


--
-- Name: COLUMN dh09.fechadechere; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fechadechere IS 'Fecha declaración de herederos';


--
-- Name: COLUMN dh09.coddependesemp; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.coddependesemp IS 'Código dependencia de desempeño';


--
-- Name: COLUMN dh09.conyugedependiente; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.conyugedependiente IS 'Cónyuge en relación de dependencia [S/N]';


--
-- Name: COLUMN dh09.fec_ingreso; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_ingreso IS 'Fecha de ingreso del agente';


--
-- Name: COLUMN dh09.codc_uacad_seguro; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.codc_uacad_seguro IS 'Dependencia seguro obligatorio';


--
-- Name: COLUMN dh09.fecha_recibo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fecha_recibo IS 'Fecha para recibo de haberes';


--
-- Name: COLUMN dh09.tipo_norma; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.tipo_norma IS 'Tipo de norma aprobatoria';


--
-- Name: COLUMN dh09.nro_norma; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.nro_norma IS 'Número de norma aprobatoria';


--
-- Name: COLUMN dh09.tipo_emite; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.tipo_emite IS 'Tipo emisor norma aprobatoria';


--
-- Name: COLUMN dh09.fec_norma; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fec_norma IS 'Fecha norma aprobatoria';


--
-- Name: COLUMN dh09.fuerza_reparto; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh09.fuerza_reparto IS 'Fuerza reparto para decreto 313/07';
