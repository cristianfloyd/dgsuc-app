--
-- Name: dh21h_id_liquidacion_seq; Type: SEQUENCE; Schema: mapuche; Owner: -
--

CREATE SEQUENCE mapuche.dh21h_id_liquidacion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dh21h; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh21h (
    id_liquidacion integer DEFAULT nextval('mapuche.dh21h_id_liquidacion_seq'::regclass) NOT NULL,
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
-- Name: TABLE dh21h; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh21h IS '(H) Historica de Liquidación al Personal';
