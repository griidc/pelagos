-- -----------------------------------------------------------------------------
-- Name:      make_person2research_group2rolen.sql
-- Author:    Patrick Krepps
-- Date:      02 Oct 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the person2research_group2role table and all
--            required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP VIEW IF EXISTS person2research_group2role;
DROP TABLE IF EXISTS person2research_group2role_table;

-- Create the table:
CREATE TABLE person2research_group2role_table
(
   person2research_group2role_number        SERIAL              NOT NULL,
   person_number                            INTEGER             NOT NULL,
   research_group_number                    INTEGER             NOT NULL,
   research_group_role_number               INTEGER             NOT NULL,
   p2rg2r_creation_time                     TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   p2rg2r_creator                           TEXT                NOT NULL,
   p2rg2r_modification_time                 TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   p2rg2r_modifier                          TEXT                NOT NULL,
   person2research_group2role_label         TEXT                NOT NULL
);

ALTER TABLE person2research_group2role_table
   ADD CONSTRAINT uidx_pk_person2research_group2role_table
      PRIMARY KEY (person2research_group2role_number);

ALTER TABLE person2research_group2role_table
   ADD CONSTRAINT fk_person2research_group2role_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

ALTER TABLE person2research_group2role_table
   ADD CONSTRAINT fk_person2research_group2role_rg_number
      FOREIGN KEY (research_group_number)
      REFERENCES research_group_table(research_group_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

ALTER TABLE person2research_group2role_table
   ADD CONSTRAINT fk_person2research_group2role_rg_role_number
      FOREIGN KEY (research_group_role_number)
      REFERENCES research_group_role_table (research_group_role_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

CREATE UNIQUE INDEX uidx_person_rg_role
   ON person2research_group2role_table(person_number,
                                       research_group_number);

-- Rename automatically created system names:
ALTER SEQUENCE person2research_group2role_ta_person2research_group2role_nu_seq
   RENAME TO seq_person2research_group2role_number;

ALTER SEQUENCE seq_person2research_group2role_number
   START 1;

ALTER TABLE person2research_group2role_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_person2research_group2role_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person2research_group2role_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person2research_group2role_table
TO gomri_reader;
