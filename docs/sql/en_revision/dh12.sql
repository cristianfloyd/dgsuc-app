--
-- Name: dh12; Type: TABLE; Schema: mapuche; Owner: -
--

CREATE TABLE mapuche.dh12 (
    codn_conce integer NOT NULL,
    vig_coano integer,
    vig_comes integer,
    desc_conce character varying(30),
    desc_corta character(15),
    tipo_conce character(1) DEFAULT 'C'::bpchar NOT NULL,
    codc_vige1 character(1),
    desc_nove1 character(15),
    tipo_nove1 character(1),
    cant_ente1 integer,
    cant_deci1 integer,
    codc_vige2 character(1),
    desc_nove2 character(15),
    tipo_nove2 character(1),
    cant_ente2 integer,
    cant_deci2 integer,
    flag_acumu character varying(99),
    flag_grupo character varying(90),
    nro_orcal integer NOT NULL,
    nro_orimp integer NOT NULL,
    sino_legaj character(1) DEFAULT 'N'::bpchar NOT NULL,
    tipo_distr character(1),
    tipo_ganan integer,
    chk_acumsac boolean,
    chk_acumproy boolean,
    chk_dcto3 boolean,
    chkacumprhbrprom boolean,
    subcicloliquida integer,
    chkdifhbrcargoasoc boolean,
    chkptesubconcep boolean,
    chkinfcuotasnovper boolean,
    genconimp0 boolean,
    sino_visible integer
);


--
-- Name: TABLE dh12; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh12 IS '(D) Conceptos de Liquidación';


--
-- Name: COLUMN dh12.codn_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.codn_conce IS 'Código de concepto';


--
-- Name: COLUMN dh12.vig_coano; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.vig_coano IS 'Año de vigencia del concepto';


--
-- Name: COLUMN dh12.vig_comes; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.vig_comes IS 'Mes de vigencia del concepto';


--
-- Name: COLUMN dh12.desc_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.desc_conce IS 'Descripción del concepto';


--
-- Name: COLUMN dh12.desc_corta; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.desc_corta IS 'Descripción abreviada del concepto';


--
-- Name: COLUMN dh12.tipo_conce; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.tipo_conce IS 'Tipo de concepto';


--
-- Name: COLUMN dh12.codc_vige1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.codc_vige1 IS 'Código de vigencia';


--
-- Name: COLUMN dh12.desc_nove1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.desc_nove1 IS 'Descripción novedad 1';


--
-- Name: COLUMN dh12.tipo_nove1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.tipo_nove1 IS 'Tipo novedad 1 Importe(I), Porc(P), Cant(C)';


--
-- Name: COLUMN dh12.cant_ente1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.cant_ente1 IS 'Cantidad de dígitos enteros 1';


--
-- Name: COLUMN dh12.cant_deci1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.cant_deci1 IS 'Cantidad de dígitos decimales 1';


--
-- Name: COLUMN dh12.codc_vige2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.codc_vige2 IS 'Código de vigencia';


--
-- Name: COLUMN dh12.desc_nove2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.desc_nove2 IS 'Descripción novedad 2';


--
-- Name: COLUMN dh12.tipo_nove2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.tipo_nove2 IS 'Tipo novedad 2 Importe(I), Porc(P), Cant(C)';


--
-- Name: COLUMN dh12.cant_ente2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.cant_ente2 IS 'Cantidad de dígitos enteros 2';


--
-- Name: COLUMN dh12.cant_deci2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.cant_deci2 IS 'Cantidad de dígitos decimales 2';


--
-- Name: COLUMN dh12.flag_acumu; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.flag_acumu IS 'Flags para acumuladores';


--
-- Name: COLUMN dh12.flag_grupo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.flag_grupo IS 'Flags para grupo de empleados';


--
-- Name: COLUMN dh12.nro_orcal; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.nro_orcal IS 'Número orden de cálculo';


--
-- Name: COLUMN dh12.nro_orimp; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.nro_orimp IS 'Número orden de impresión';


--
-- Name: COLUMN dh12.sino_legaj; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.sino_legaj IS 'Concepto para legajo de empleado';


--
-- Name: COLUMN dh12.tipo_distr; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.tipo_distr IS 'Tipo distribución (Mayor Cargo, Proporc)';


--
-- Name: COLUMN dh12.tipo_ganan; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.tipo_ganan IS 'Tipo de concepto para ganancias';


--
-- Name: COLUMN dh12.chk_acumsac; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chk_acumsac IS 'Acumula para SAC?[S/N]';


--
-- Name: COLUMN dh12.chk_acumproy; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chk_acumproy IS 'Acumula para proyec. presup.?[S/N]';


--
-- Name: COLUMN dh12.chk_dcto3; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chk_dcto3 IS 'Descuento de terceros?[S/N]';


--
-- Name: COLUMN dh12.chkacumprhbrprom; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chkacumprhbrprom IS 'Acumula para haber promedio?[S/N]';


--
-- Name: COLUMN dh12.subcicloliquida; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.subcicloliquida IS 'Sub-ciclo de liquidación';


--
-- Name: COLUMN dh12.chkdifhbrcargoasoc; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chkdifhbrcargoasoc IS 'Diferencia haber en cargo asociado?[S/N]';


--
-- Name: COLUMN dh12.chkptesubconcep; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chkptesubconcep IS 'Permite subconceptos?[S/N]';


--
-- Name: COLUMN dh12.chkinfcuotasnovper; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.chkinfcuotasnovper IS 'Informa cuotas novedad permanente?[S/N]';


--
-- Name: COLUMN dh12.genconimp0; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.genconimp0 IS 'Genera línea de liquidación con importe cero';


--
-- Name: COLUMN dh12.sino_visible; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh12.sino_visible IS 'Concepto habilitado o no para ser utilizado';


