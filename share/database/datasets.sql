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
-- Name: datasets; Type: TABLE; Schema: public; Owner: db_owner; Tablespace: 
--

CREATE TABLE datasets (
    dataset_uid integer NOT NULL,
    task_uid smallint,
    title character varying(200),
    abstract character varying(4000),
    dataset_type character varying(300),
    dataset_for character varying(300),
    size character varying(50),
    observation character varying(300),
    approach character varying(300),
    start_date date,
    end_date date,
    geo_location character varying(200),
    historic_links character varying(300),
    meta_editor character varying(200),
    meta_standards character varying(200),
    point character varying(300),
    "national" character varying(300),
    ethical character varying(300),
    remarks character varying(300),
    primary_poc smallint,
    secondary_poc smallint,
    logname smallint,
    status smallint,
    datafor character varying(300),
    project_id smallint,
    dataset_udi character(16)
);


ALTER TABLE public.datasets OWNER TO db_owner;

--
-- Name: TABLE datasets; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON TABLE datasets IS 'Dataset form data.';


--
-- Name: COLUMN datasets.dataset_uid; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.dataset_uid IS 'Dataset Identifier.';


--
-- Name: COLUMN datasets.task_uid; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.task_uid IS 'Task Identifier.';


--
-- Name: COLUMN datasets.title; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.title IS 'Dataset Title.';


--
-- Name: COLUMN datasets.abstract; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.abstract IS 'Dataset Abstract.';


--
-- Name: COLUMN datasets.dataset_type; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.dataset_type IS 'Dataset Types.';


--
-- Name: COLUMN datasets.dataset_for; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.dataset_for IS 'Dataset for';


--
-- Name: COLUMN datasets.size; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.size IS 'Dataset size.';


--
-- Name: COLUMN datasets.observation; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.observation IS 'Observations (Phenomenon and Variable)';


--
-- Name: COLUMN datasets.approach; Type: COMMENT; Schema: public; Owner: db_owner
--

COMMENT ON COLUMN datasets.approach IS 'Dataset Approach(es).';


--
-- Name: datasets_pkey1; Type: CONSTRAINT; Schema: public; Owner: db_owner; Tablespace: 
--

ALTER TABLE ONLY datasets
    ADD CONSTRAINT datasets_pkey1 PRIMARY KEY (dataset_uid);


--
-- Name: datasets_dataset_udi_idx; Type: INDEX; Schema: public; Owner: db_owner; Tablespace: 
--

CREATE UNIQUE INDEX datasets_dataset_udi_idx ON datasets USING btree (dataset_udi);


--
-- Name: datasets; Type: ACL; Schema: public; Owner: db_owner
--

REVOKE ALL ON TABLE datasets FROM PUBLIC;
REVOKE ALL ON TABLE datasets FROM db_owner;
GRANT ALL ON TABLE datasets TO db_owner;
GRANT SELECT,INSERT,UPDATE ON TABLE datasets TO gomri_user;


--
-- PostgreSQL database dump complete
--

