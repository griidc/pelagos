--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: doi_regs; Type: TABLE; Schema: public; Owner: db_owner; Tablespace: 
--

CREATE TABLE doi_regs (
    id integer NOT NULL,
    url character varying(200),
    creator character varying(100),
    title character varying(120),
    publisher character varying(120),
    dsdate date,
    reqdate timestamp with time zone,
    reqby character varying(60),
    reqip character varying(16),
    reqemail character varying(60),
    reqfirstname character varying(120),
    reqlastname character varying(200),
    emailsend boolean,
    approved boolean,
    approvedby character varying,
    approvedon timestamp without time zone,
    doi text,
    urlstatus text,
    formhash text
);


ALTER TABLE public.doi_regs OWNER TO db_owner;

--
-- Name: doi_regs_id_seq; Type: SEQUENCE; Schema: public; Owner: db_owner
--

CREATE SEQUENCE doi_regs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.doi_regs_id_seq OWNER TO db_owner;

--
-- Name: doi_regs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db_owner
--

ALTER SEQUENCE doi_regs_id_seq OWNED BY doi_regs.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: db_owner
--

ALTER TABLE ONLY doi_regs ALTER COLUMN id SET DEFAULT nextval('doi_regs_id_seq'::regclass);


--
-- Name: Primary ID; Type: CONSTRAINT; Schema: public; Owner: db_owner; Tablespace: 
--

ALTER TABLE ONLY doi_regs
    ADD CONSTRAINT "Primary ID" PRIMARY KEY (id);


--
-- Name: doi_regs_formhash_key; Type: CONSTRAINT; Schema: public; Owner: db_owner; Tablespace: 
--

ALTER TABLE ONLY doi_regs
    ADD CONSTRAINT doi_regs_formhash_key UNIQUE (formhash);


--
-- Name: doi_regs; Type: ACL; Schema: public; Owner: db_owner
--

REVOKE ALL ON TABLE doi_regs FROM PUBLIC;
REVOKE ALL ON TABLE doi_regs FROM db_owner;
GRANT ALL ON TABLE doi_regs TO db_owner;
GRANT ALL ON TABLE doi_regs TO gomri_user;


--
-- Name: doi_regs_id_seq; Type: ACL; Schema: public; Owner: db_owner
--

REVOKE ALL ON SEQUENCE doi_regs_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE doi_regs_id_seq FROM db_owner;
GRANT ALL ON SEQUENCE doi_regs_id_seq TO db_owner;
GRANT SELECT,USAGE ON SEQUENCE doi_regs_id_seq TO gomri_user;


--
-- PostgreSQL database dump complete
--

