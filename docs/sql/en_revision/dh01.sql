--
-- Name: dh01; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh01 (
    nro_legaj integer NOT NULL,
    desc_appat character(20),
    desc_apmat character(20),
    desc_apcas character(20),
    desc_nombr character(20),
    nro_tabla integer,
    tipo_docum character(4),
    nro_docum integer,
    nro_cuil1 integer,
    nro_cuil integer,
    nro_cuil2 integer,
    tipo_sexo character(1),
    fec_nacim date,
    tipo_facto character(2),
    tipo_rh character(1),
    nro_ficha integer,
    tipo_estad character(1),
    nombrelugarnac character varying(60),
    periodoalta integer,
    anioalta integer,
    periodoactualizacion integer,
    anioactualizacion integer,
    pcia_nacim character(1),
    pais_nacim character(2)
);


--
-- Name: TABLE dh01; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh01 IS '(D) Datos Personales de Empleados';


--
-- Name: COLUMN dh01.nro_legaj; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_legaj IS 'Número de legajo';


--
-- Name: COLUMN dh01.desc_appat; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.desc_appat IS 'Apellido del empleado';


--
-- Name: COLUMN dh01.desc_apmat; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.desc_apmat IS 'Apellido materno del empleado';


--
-- Name: COLUMN dh01.desc_apcas; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.desc_apcas IS 'Apellido de casada/o del empleado';


--
-- Name: COLUMN dh01.desc_nombr; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.desc_nombr IS 'Nombres del empleado';


--
-- Name: COLUMN dh01.nro_tabla; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_tabla IS 'Referencia a dh30';


--
-- Name: COLUMN dh01.tipo_docum; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.tipo_docum IS 'Tipo de documento';


--
-- Name: COLUMN dh01.nro_docum; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_docum IS 'Número de documento';


--
-- Name: COLUMN dh01.nro_cuil1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_cuil1 IS 'Número del CUIL';


--
-- Name: COLUMN dh01.nro_cuil; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_cuil IS 'Número del CUIL';


--
-- Name: COLUMN dh01.nro_cuil2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_cuil2 IS 'Número del CUIL';


--
-- Name: COLUMN dh01.tipo_sexo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.tipo_sexo IS 'Sexo Masculino (M), Femenino (F)';


--
-- Name: COLUMN dh01.fec_nacim; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.fec_nacim IS 'Fecha de nacimiento';


--
-- Name: COLUMN dh01.tipo_facto; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.tipo_facto IS 'Factor sanguíneo';


--
-- Name: COLUMN dh01.tipo_rh; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.tipo_rh IS 'RH';


--
-- Name: COLUMN dh01.nro_ficha; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nro_ficha IS 'Número de ficha';


--
-- Name: COLUMN dh01.tipo_estad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.tipo_estad IS 'Estado Activo (A), Pasivo (P), Jubilado (J)';


--
-- Name: COLUMN dh01.nombrelugarnac; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.nombrelugarnac IS 'Lugar de nacimiento';


--
-- Name: COLUMN dh01.periodoalta; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.periodoalta IS 'Mes alta';


--
-- Name: COLUMN dh01.anioalta; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.anioalta IS 'Año alta';


--
-- Name: COLUMN dh01.periodoactualizacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.periodoactualizacion IS 'Mes última actualización';


--
-- Name: COLUMN dh01.anioactualizacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.anioactualizacion IS 'Año última actualización';


--
-- Name: COLUMN dh01.pcia_nacim; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.pcia_nacim IS 'Código provincia de nacimiento';


--
-- Name: COLUMN dh01.pais_nacim; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh01.pais_nacim IS 'Código país de nacimiento';

