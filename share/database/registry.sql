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
-- Name: registry; Type: TABLE; Schema: public; Owner: db_owner; Tablespace: 
--

CREATE TABLE registry (
    registry_id character(20) NOT NULL,
    data_server_type character varying(100),
    url_data character varying(400),
    url_metadata character varying(400),
    data_source_pull boolean,
    doi character varying(200),
    username character varying(200),
    password character varying(200),
    availability_date date,
    access_status character varying(100),
    access_period boolean,
    access_period_start time with time zone,
    access_period_weekdays character varying(200),
    dataset_title character varying(200),
    dataset_abstract character varying(4000),
    dataset_poc_name character varying(200),
    dataset_poc_email character varying(200),
    dataset_udi character(16),
    submittimestamp timestamp without time zone,
    userid character varying(100),
    authentication boolean,
    generatedoi boolean,
    dataset_download_start_datetime timestamp without time zone,
    dataset_download_size bigint,
    dataset_download_end_datetime timestamp without time zone,
    dataset_filename character varying(300),
    dataset_uuid uuid,
    dataset_metadata character varying(300),
    dataset_download_error_log character varying(300),
    dataset_download_status dl_status DEFAULT 'no_status'::dl_status
);


ALTER TABLE public.registry OWNER TO db_owner;

--
-- Name: pk_registry_id; Type: CONSTRAINT; Schema: public; Owner: db_owner; Tablespace: 
--

ALTER TABLE ONLY registry
    ADD CONSTRAINT pk_registry_id PRIMARY KEY (registry_id);


--
-- Name: uni_reg_id; Type: CONSTRAINT; Schema: public; Owner: db_owner; Tablespace: 
--

ALTER TABLE ONLY registry
    ADD CONSTRAINT uni_reg_id UNIQUE (registry_id);


--
-- Name: registry; Type: ACL; Schema: public; Owner: db_owner
--

REVOKE ALL ON TABLE registry FROM PUBLIC;
REVOKE ALL ON TABLE registry FROM db_owner;
GRANT ALL ON TABLE registry TO db_owner;
GRANT ALL ON TABLE registry TO gomri_user;


--
-- PostgreSQL database dump complete
--

