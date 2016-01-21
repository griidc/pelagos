-- -----------------------------------------------------------------------------
-- Name:      make_data_repository_role.sql
-- Author:    Patrick Krepps
-- Date:      30 Oct 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the data_repository_role table and all
--            required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP VIEW IF EXISTS data_repository_role;
DROP TABLE IF EXISTS data_repository_role_table CASCADE;

-- Create the table:
CREATE TABLE data_repository_role_table
(
   data_repository_role_number              SERIAL              NOT NULL,
   data_repository_role_creation_time       TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   data_repository_role_creator             TEXT                NOT NULL,
   data_repository_role_modification_time   TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   data_repository_role_modifier            TEXT                NOT NULL,
   data_repository_role_name                TEXT                NOT NULL,
   data_repository_role_weight              INTEGER             NOT NULL
);

ALTER TABLE data_repository_role_table
   ADD CONSTRAINT uidx_pk_data_repository_role_table
      PRIMARY KEY (data_repository_role_number);

ALTER SEQUENCE data_repository_role_table_data_repository_role_number_seq
   RENAME TO seq_data_repository_role_number;

ALTER SEQUENCE seq_data_repository_role_number
   START 1;

ALTER TABLE data_repository_role_table
   OWNER TO gomri_admin;

CREATE UNIQUE INDEX uidx_data_repository_role_name
   ON data_repository_role_table(LOWER(data_repository_role_name));

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_data_repository_role_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE data_repository_role_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE data_repository_role_table
TO gomri_reader;
