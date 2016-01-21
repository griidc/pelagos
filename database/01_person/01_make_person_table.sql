-- -----------------------------------------------------------------------------
-- Name:      make_person.sql
-- Author:    Patrick Krepps
-- Date:      05 May 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the person entity table and all required
--            elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW IF EXISTS person;
DROP TABLE IF EXISTS email2person_table CASCADE;
DROP TABLE IF EXISTS email_table CASCADE;
DROP TABLE IF EXISTS person_table CASCADE;
DROP DOMAIN IF EXISTS PHONE_NUMBER_TYPE;

-- Now create the telephone number type:
CREATE DOMAIN PHONE_NUMBER_TYPE
AS TEXT
DEFAULT NULL
CONSTRAINT chk_phone_number
   CHECK (VALUE ~ '^[1-9][0-9]{2}[1-9][0-9]{6}$');

-- Now create person_table:
CREATE TABLE person_table
(
   person_number                            SERIAL,
   person_administrative_area               TEXT                DEFAULT NULL,
   person_city                              TEXT                DEFAULT NULL,
   person_country                           TEXT                DEFAULT NULL,
   person_creation_time                     TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person_creator                           TEXT                NOT NULL,
   person_delivery_point                    TEXT                DEFAULT NULL,
   person_given_name                        TEXT                NOT NULL,
   person_honorific_title                   TEXT                DEFAULT NULL,
   person_middle_name                       TEXT                DEFAULT NULL,
   person_modification_time                 TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person_modifier                          TEXT                NOT NULL,
   person_name_suffix                       TEXT                DEFAULT NULL,
   person_organization                      TEXT                DEFAULT NULL,
   person_phone_number                      PHONE_NUMBER_TYPE   DEFAULT NULL,
   person_position                          TEXT                DEFAULT NULL,
   person_postal_code                       TEXT                DEFAULT NULL,
   person_surname                           TEXT                NOT NULL,
   person_website                           TEXT                DEFAULT NULL,

   CONSTRAINT chk_person_mod_time_not_before_create
      CHECK (person_modification_time >= person_creation_time),

   PRIMARY KEY (person_number)
);

ALTER INDEX person_table_pkey
   RENAME TO uidx_pk_person;

ALTER SEQUENCE person_table_person_number_seq
   RENAME TO seq_person_number;

ALTER SEQUENCE seq_person_number
   START 1;

CREATE INDEX idx_person_surname
   ON person_table(person_surname);

-- Set object ownership:
ALTER TABLE person_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_person_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_table
TO gomri_reader;
