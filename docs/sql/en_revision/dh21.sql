--
-- Name: dh21; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh21 (
    id_liquidacion integer DEFAULT nextval('mapuche.dh21_id_liquidacion_seq'::regclass) NOT NULL,
    nro_liqui integer,
    nro_legaj integer,
    nro_cargo integer,
    codn_conce integer,
    impp_conce double precision,
    tipo_conce character(1),
    nov1_conce double precision,
    nov2_conce double precision,
    nro_orimp integer,
    tipoescalafon character(1),
    nrogrupoesc integer,
    codigoescalafon character(4),
    codc_regio character(4),
    codc_uacad character(4),
    codn_area integer,
    codn_subar integer,
    codn_fuent integer,
    codn_progr integer,
    codn_subpr integer,
    codn_proye integer,
    codn_activ integer,
    codn_obra integer,
    codn_final integer,
    codn_funci integer,
    ano_retro integer,
    mes_retro integer,
    detallenovedad character(10),
    codn_grupo_presup integer DEFAULT 1,
    tipo_ejercicio character(1) DEFAULT 'A'::bpchar,
    codn_subsubar integer DEFAULT 0
);


--
-- Name: TABLE dh21; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh21 IS '(D) Liquidación al Personal';


--
-- Name: COLUMN dh21.id_liquidacion; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.id_liquidacion IS 'Autonumérico Registro de Liquidación';


--
-- Name: COLUMN dh21.nro_liqui; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nro_liqui IS 'Número de Liquidación';


--
-- Name: COLUMN dh21.nro_legaj; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nro_legaj IS 'Número de Legajo';


--
-- Name: COLUMN dh21.nro_cargo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nro_cargo IS 'Número de Cargo';


--
-- Name: COLUMN dh21.codn_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_conce IS 'Código de Concepto';


--
-- Name: COLUMN dh21.impp_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.impp_conce IS 'Importe Calculado del Concepto';


--
-- Name: COLUMN dh21.tipo_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.tipo_conce IS 'Tipo de Concepto:C,S,D,F,A';


--
-- Name: COLUMN dh21.nov1_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nov1_conce IS 'Novedad 1 del Concepto';


--
-- Name: COLUMN dh21.nov2_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nov2_conce IS 'Novedad 2 del Concepto';


--
-- Name: COLUMN dh21.nro_orimp; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nro_orimp IS 'Orden de Impresión';


--
-- Name: COLUMN dh21.tipoescalafon; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.tipoescalafon IS 'Tipo de Escalafón (D,N,C, )';


--
-- Name: COLUMN dh21.nrogrupoesc; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.nrogrupoesc IS 'Nro. de Grupo Escalafón (Caracter)';


--
-- Name: COLUMN dh21.codigoescalafon; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codigoescalafon IS 'Codigo de Escalafón';


--
-- Name: COLUMN dh21.codc_regio; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codc_regio IS 'Regional';


--
-- Name: COLUMN dh21.codc_uacad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codc_uacad IS 'Unidad Académica';


--
-- Name: COLUMN dh21.codn_area; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_area IS 'Unidad';


--
-- Name: COLUMN dh21.codn_subar; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_subar IS 'Sub Unidad';


--
-- Name: COLUMN dh21.codn_fuent; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_fuent IS 'Fuente: Origen de Fondos';


--
-- Name: COLUMN dh21.codn_progr; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_progr IS 'Imp.Pres.1';


--
-- Name: COLUMN dh21.codn_subpr; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_subpr IS 'Imp.Pres.2';


--
-- Name: COLUMN dh21.codn_proye; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_proye IS 'Imp.Pres.3';


--
-- Name: COLUMN dh21.codn_activ; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_activ IS 'Imp.Pres.4';


--
-- Name: COLUMN dh21.codn_obra; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_obra IS 'Imp.Pres.5';


--
-- Name: COLUMN dh21.codn_final; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_final IS 'Finalidad';


--
-- Name: COLUMN dh21.codn_funci; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_funci IS 'Función';


--
-- Name: COLUMN dh21.ano_retro; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.ano_retro IS 'Año para Retroactrivo de Concepto';


--
-- Name: COLUMN dh21.mes_retro; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.mes_retro IS 'Mes para Retroactrivo de Concepto';


--
-- Name: COLUMN dh21.detallenovedad; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.detallenovedad IS 'Detalle de la Novedad';


--
-- Name: COLUMN dh21.codn_subsubar; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh21.codn_subsubar IS 'Sub Sub Unidad';


