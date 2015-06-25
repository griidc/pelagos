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
DROP VIEW person;
DROP TABLE email2person_table;
DROP TABLE email_table;
DROP TABLE person_table CASCADE;
DROP DOMAIN EMAIL_ADDRESS_TYPE;

-- Now create person_table, and make the necessary alterations:
CREATE TABLE person_table
(
   person_number                            SERIAL,
   person_creation_time                     TIMESTAMP WITH TIME ZONE
      NOT NULL                              DEFAULT NOW(),
   person_given_name                        TEXT                NOT NULL,
   person_honorific_title                   TEXT                DEFAULT NULL,
   person_instantiation_time                TIMESTAMP WITH TIME ZONE
      NOT NULL                              DEFAULT NOW(),
   person_instantiator                      TEXT                NOT NULL,
   person_middle_name                       TEXT                DEFAULT NULL,
   person_modification_time                 TIMESTAMP WITH TIME ZONE
      NOT NULL                              DEFAULT NOW(),
   person_modifier                          TEXT                NOT NULL,
   person_name_suffix                       TEXT                DEFAULT NULL,
   person_surname                           TEXT                NOT NULL,

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

-- Set object ownerships:
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
