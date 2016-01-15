-- -----------------------------------------------------------------------------
-- Name:      make_person2funding_organization2role.sql
-- Author:    Patrick Krepps
-- Date:      14 Oct 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the person2funding_organization2role table
--            an all required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP VIEW IF EXISTS person2funding_organization2role;
DROP TABLE IF EXISTS person2funding_organization2role_table;

-- Create the table:
CREATE TABLE person2funding_organization2role_table
(
   person2funding_organization2role_number  SERIAL              NOT NULL,
   person_number                            INTEGER             NOT NULL,
   funding_organization_number              INTEGER             NOT NULL,
   funding_organization_role_number         INTEGER             NOT NULL,
   person2funding_organization2role_creation_time   TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person2funding_organization2role_creator TEXT               NOT NULL,
   person2funding_organization2role_modification_time   TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person2funding_organization2role_modifier   TEXT             NOT NULL,
   person2funding_organization2role_label   TEXT                NOT NULL
);

ALTER TABLE person2funding_organization2role_table
   ADD CONSTRAINT uidx_pk_person2funding_organization2role_table
      PRIMARY KEY (person2funding_organization2role_number);

ALTER TABLE person2funding_organization2role_table
   ADD CONSTRAINT fk_person2funding_organization2role_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

ALTER TABLE person2funding_organization2role_table
   ADD CONSTRAINT fk_person2funding_organization2role_fo_number
      FOREIGN KEY (funding_organization_number)
      REFERENCES funding_organization_table(funding_organization_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

ALTER TABLE person2funding_organization2role_table
   ADD CONSTRAINT fk_person2funding_organization2role_fo_role_number
      FOREIGN KEY (funding_organization_role_number)
      REFERENCES funding_organization_role_table
                    (funding_organization_role_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

CREATE UNIQUE INDEX uidx_person_fo_role
   ON person2funding_organization2role_table(person_number,
                                       funding_organization_number);

-- Rename automatically created system names:
ALTER SEQUENCE person2funding_organization2r_person2funding_organization2r_seq
   RENAME TO seq_person2funding_organization2role_number;

ALTER SEQUENCE seq_person2funding_organization2role_number
   START 1;

ALTER TABLE person2funding_organization2role_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_person2funding_organization2role_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person2funding_organization2role_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person2funding_organization2role_table
TO gomri_reader;
