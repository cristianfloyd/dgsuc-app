--
-- Name: dh22; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh22 (
    nro_liqui integer NOT NULL,
    per_liano integer,
    per_limes integer,
    desc_liqui character varying(60),
    fec_ultap date,
    per_anoap integer,
    per_mesap integer,
    desc_lugap character(20),
    fec_emisi date,
    desc_emisi character(20),
    vig_emano integer,
    vig_emmes integer,
    vig_caano integer,
    vig_cames integer,
    vig_coano integer,
    vig_comes integer,
    codn_econo integer,
    sino_cerra character(1) NOT NULL,
    sino_aguin boolean,
    sino_reten boolean,
    sino_genimp boolean,
    nrovalorpago integer,
    finimpresrecibos integer
);


--
-- Name: TABLE dh22; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh22 IS '(D) Parámetros de Liquidaciones';


--
-- Name: COLUMN dh22.nro_liqui; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.nro_liqui IS 'Número de Liquidación';


--
-- Name: COLUMN dh22.per_liano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.per_liano IS 'Año del Período de Liquidación';


--
-- Name: COLUMN dh22.per_limes; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.per_limes IS 'Mes del Período de Liquidación';


--
-- Name: COLUMN dh22.desc_liqui; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.desc_liqui IS 'Descripción de la Liquidación';


--
-- Name: COLUMN dh22.fec_ultap; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.fec_ultap IS 'Fecha de Ultimo Aporte';


--
-- Name: COLUMN dh22.per_anoap; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.per_anoap IS 'Año ultimo aporte';


--
-- Name: COLUMN dh22.per_mesap; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.per_mesap IS 'Mes Ultimo Aporte';


--
-- Name: COLUMN dh22.desc_lugap; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.desc_lugap IS 'Descripción Lugar de Ultimo Aporte';


--
-- Name: COLUMN dh22.fec_emisi; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.fec_emisi IS 'Fecha de Emisión';


--
-- Name: COLUMN dh22.desc_emisi; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.desc_emisi IS 'Descripción de la Emisión';


--
-- Name: COLUMN dh22.vig_emano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_emano IS 'Año de Vigencia de Empleados';


--
-- Name: COLUMN dh22.vig_emmes; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_emmes IS 'Mes de Vigencia de Empleados';


--
-- Name: COLUMN dh22.vig_caano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_caano IS 'Año de Vigencia de Cargos';


--
-- Name: COLUMN dh22.vig_cames; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_cames IS 'Mes de Vigencia de Cargos';


--
-- Name: COLUMN dh22.vig_coano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_coano IS 'Año de Vigencia de Conceptos';


--
-- Name: COLUMN dh22.vig_comes; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.vig_comes IS 'Mes de Vigencia de Conceptos';


--
-- Name: COLUMN dh22.codn_econo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.codn_econo IS 'Código Económico';


--
-- Name: COLUMN dh22.sino_cerra; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.sino_cerra IS 'Liquidación Cerrada [S/N]';


--
-- Name: COLUMN dh22.sino_aguin; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.sino_aguin IS 'Acumulado Aguinaldo ?';


--
-- Name: COLUMN dh22.sino_reten; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.sino_reten IS 'Retención 4ta. Categoría ?';


--
-- Name: COLUMN dh22.sino_genimp; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.sino_genimp IS 'Generación de Datos Imp. ?';


--
-- Name: COLUMN dh22.nrovalorpago; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh22.nrovalorpago IS 'Nro. de Valor de Pago';


