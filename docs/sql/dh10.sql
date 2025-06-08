dh10.sql
CREATE TABLE mapuche.dh10 (
    nro_cargo integer NOT NULL,
    vcl_cargo integer NOT NULL,
    imp_bruto_1 double precision,
    imp_bruto_2 double precision,
    imp_bruto_3 double precision,
    imp_bruto_4 double precision,
    imp_bruto_5 double precision,
    imp_bruto_6 double precision,
    imp_bruto_7 double precision,
    imp_bruto_8 double precision,
    imp_bruto_9 double precision,
    imp_bruto_10 double precision,
    imp_bruto_11 double precision,
    imp_bruto_12 double precision,
    importes_retro_1 double precision,
    importes_retro_2 double precision,
    importes_retro_3 double precision,
    importes_retro_4 double precision,
    importes_retro_5 double precision,
    importes_retro_6 double precision,
    importes_retro_7 double precision,
    importes_retro_8 double precision,
    importes_retro_9 double precision,
    importes_retro_10 double precision,
    importes_retro_11 double precision,
    importes_retro_12 double precision,
    impbrhbrprom_1 double precision,
    impbrhbrprom_2 double precision,
    impbrhbrprom_3 double precision,
    impbrhbrprom_4 double precision,
    impbrhbrprom_5 double precision,
    impbrhbrprom_6 double precision,
    impbrhbrprom_7 double precision,
    impbrhbrprom_8 double precision,
    impbrhbrprom_9 double precision,
    impbrhbrprom_10 double precision,
    impbrhbrprom_11 double precision,
    impbrhbrprom_12 double precision,
    impbrhbrprom_13 double precision,
    impbrhbrprom_14 double precision,
    impbrhbrprom_15 double precision,
    impbrhbrprom_16 double precision,
    impbrhbrprom_17 double precision,
    impbrhbrprom_18 double precision,
    retroimpbrhbrpr_1 double precision,
    retroimpbrhbrpr_2 double precision,
    retroimpbrhbrpr_3 double precision,
    retroimpbrhbrpr_4 double precision,
    retroimpbrhbrpr_5 double precision,
    retroimpbrhbrpr_6 double precision,
    retroimpbrhbrpr_7 double precision,
    retroimpbrhbrpr_8 double precision,
    retroimpbrhbrpr_9 double precision,
    retroimpbrhbrpr_10 double precision,
    retroimpbrhbrpr_11 double precision,
    retroimpbrhbrpr_12 double precision,
    retroimpbrhbrpr_13 double precision,
    retroimpbrhbrpr_14 double precision,
    retroimpbrhbrpr_15 double precision,
    retroimpbrhbrpr_16 double precision,
    retroimpbrhbrpr_17 double precision,
    retroimpbrhbrpr_18 double precision
);


--
-- Name: TABLE dh10; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON TABLE mapuche.dh10 IS '(D) Brutos Acumulados para SAC';


--
-- Name: COLUMN dh10.nro_cargo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.nro_cargo IS 'Cargo del Empleado';


--
-- Name: COLUMN dh10.vcl_cargo; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.vcl_cargo IS 'Vínculo Cargo Orígen';


--
-- Name: COLUMN dh10.imp_bruto_1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_1 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_2 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_3; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_3 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_4; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_4 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_5; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_5 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_6; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_6 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_7; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_7 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_8; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_8 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_9; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_9 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_10; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_10 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_11; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_11 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.imp_bruto_12; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.imp_bruto_12 IS 'Importe Bruto Acumulado para SAC';


--
-- Name: COLUMN dh10.importes_retro_1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_1 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_2 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_3; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_3 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_4; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_4 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_5; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_5 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_6; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_6 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_7; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_7 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_8; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_8 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_9; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_9 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_10; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_10 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_11; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_11 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.importes_retro_12; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.importes_retro_12 IS 'Importes Retroactivos Acum. Periodo Cte.';


--
-- Name: COLUMN dh10.impbrhbrprom_1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_1 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_2 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_3; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_3 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_4; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_4 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_5; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_5 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_6; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_6 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_7; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_7 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_8; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_8 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_9; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_9 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_10; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_10 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_11; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_11 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_12; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_12 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_13; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_13 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_14; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_14 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_15; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_15 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_16; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_16 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_17; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_17 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.impbrhbrprom_18; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.impbrhbrprom_18 IS 'Importes Brutos Haber Promedio';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_1; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_1 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_2; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_2 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_3; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_3 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_4; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_4 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_5; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_5 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_6; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_6 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_7; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_7 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_8; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_8 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_9; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_9 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_10; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_10 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_11; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_11 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_12; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_12 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_13; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_13 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_14; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_14 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_15; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_15 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_16; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_16 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_17; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_17 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';


--
-- Name: COLUMN dh10.retroimpbrhbrpr_18; Type: COMMENT; Schema: mapuche; Owner: -
--

COMMENT ON COLUMN mapuche.dh10.retroimpbrhbrpr_18 IS 'Retro Brutos Haber Prom.Acum.Periodo Cte';
