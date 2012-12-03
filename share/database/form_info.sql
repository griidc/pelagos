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
-- Name: form_info; Type: TABLE; Schema: public; Owner: gomri_user; Tablespace: 
--

CREATE TABLE form_info (
    id integer NOT NULL,
    comments text NOT NULL,
    var_name character varying(40) NOT NULL,
    form character varying(20)
);


ALTER TABLE public.form_info OWNER TO gomri_user;

--
-- Name: COLUMN form_info.form; Type: COMMENT; Schema: public; Owner: gomri_user
--

COMMENT ON COLUMN form_info.form IS 'Form name for the var_name';


--
-- Name: form_info_comments_seq; Type: SEQUENCE; Schema: public; Owner: gomri_user
--

CREATE SEQUENCE form_info_comments_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.form_info_comments_seq OWNER TO gomri_user;

--
-- Name: form_info_comments_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: gomri_user
--

ALTER SEQUENCE form_info_comments_seq OWNED BY form_info.comments;


--
-- Name: form_info_id_seq; Type: SEQUENCE; Schema: public; Owner: gomri_user
--

CREATE SEQUENCE form_info_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.form_info_id_seq OWNER TO gomri_user;

--
-- Name: form_info_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: gomri_user
--

ALTER SEQUENCE form_info_id_seq OWNED BY form_info.id;


--
-- Name: form_info_var_name_seq; Type: SEQUENCE; Schema: public; Owner: gomri_user
--

CREATE SEQUENCE form_info_var_name_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.form_info_var_name_seq OWNER TO gomri_user;

--
-- Name: form_info_var_name_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: gomri_user
--

ALTER SEQUENCE form_info_var_name_seq OWNED BY form_info.var_name;


--
-- Name: form_info_pkey; Type: CONSTRAINT; Schema: public; Owner: gomri_user; Tablespace: 
--

ALTER TABLE ONLY form_info
    ADD CONSTRAINT form_info_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

