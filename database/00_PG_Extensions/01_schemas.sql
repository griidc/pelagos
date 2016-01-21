DROP SCHEMA IF EXISTS topology CASCADE;
CREATE SCHEMA topology;
ALTER SCHEMA topology OWNER TO postgres;

REVOKE ALL ON SCHEMA topology FROM PUBLIC;
REVOKE ALL ON SCHEMA topology FROM postgres;
GRANT ALL ON SCHEMA topology TO postgres;

GRANT USAGE ON SCHEMA topology TO gomri_user;
GRANT USAGE ON SCHEMA topology TO gomri_reader;
GRANT USAGE ON SCHEMA topology TO gomri_writer;

