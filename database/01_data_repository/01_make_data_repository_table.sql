-- -----------------------------------------------------------------------------
-- Name:      make_data_repository_table.sql
-- Author:    Patrick Krepps
-- Date:      29 October 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the data repository entity table and all
--            required elements of the table. I think address attributes, and
--            probably even website and phone information should be normalized
--            to separate tables, but the task requirements are to allow these
--            attributes to be free text so normalizing them out won't really
--            do much good since technically at that point they are no longer
--            repeating groups.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW data_repository;
DROP TABLE email2data_repository_table CASCADE;
DROP TABLE data_repository_table CASCADE;

-- Now create data_repository_table, and make the necessary alterations:
CREATE TABLE data_repository_table
(
   data_repository_number                   SERIAL,
   data_repository_administrative_area      TEXT                NOT NULL,
   data_repository_city                     TEXT                NOT NULL,
   data_repository_country                  TEXT                NOT NULL,
   data_repository_creation_time            TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   data_repository_creator                  TEXT                NOT NULL,
   data_repository_delivery_point           TEXT                NOT NULL,
   data_repository_description              TEXT                NOT NULL,
   data_repository_modification_time        TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   data_repository_modifier                 TEXT                NOT NULL,
   data_repository_name                     TEXT                NOT NULL,
   data_repository_phone_number             TEXT                NOT NULL,
   data_repository_postal_code              TEXT                NOT NULL,
   data_repository_website                  TEXT                NOT NULL,

   CONSTRAINT chk_dr_mod_time_not_before_create
      CHECK(data_repository_modification_time >=
            data_repository_creation_time),

   PRIMARY KEY (data_repository_number)
);

ALTER INDEX data_repository_table_pkey
   RENAME TO uidx_pk_data_repository;

ALTER SEQUENCE data_repository_table_data_repository_number_seq
   RENAME TO seq_data_repository_number;

ALTER SEQUENCE seq_data_repository_number
   START 1;

-- Set object ownership:
ALTER TABLE data_repository_table
   OWNER TO gomri_admin;

-- Enforce name uniqueness:
CREATE UNIQUE INDEX uidx_lower_data_repository
   ON data_repository_table(LOWER(data_repository_name));

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_data_repository_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE data_repository_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE data_repository_table
TO gomri_reader;
