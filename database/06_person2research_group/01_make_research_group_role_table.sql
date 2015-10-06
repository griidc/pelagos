-- -----------------------------------------------------------------------------
-- Name:      make_research_group_rolen.sql
-- Author:    Patrick Krepps
-- Date:      02 Oct 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the research_group_role table and all
--            required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP VIEW research_group_role;
DROP VIEW person2research_group2role;
DROP TABLE person2research_group2role_table;
DROP TABLE research_group_role_table;

-- Create the table:
CREATE TABLE research_group_role_table
(
   research_group_role_number               SERIAL              NOT NULL,
   research_group_creation_time             TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   research_group_creator                   TEXT                NOT NULL,
   research_group_modification_time         TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   research_group_modifier                  TEXT                NOT NULL,
   research_group_role_name                 TEXT                NOT NULL,
   research_group_role_weight               INTEGER             NOT NULL
);

ALTER TABLE research_group_role_table
   ADD CONSTRAINT uidx_pk_research_group_role_table
      PRIMARY KEY (research_group_role_number);

ALTER SEQUENCE research_group_role_table_research_group_role_number_seq
   RENAME TO seq_research_group_role_number;

ALTER SEQUENCE seq_research_group_role_number
   START 1;

ALTER TABLE research_group_role_table
   OWNER TO gomri_admin;

CREATE UNIQUE INDEX uidx_research_group_role_name
   ON research_group_role_table(LOWER(research_group_role_name));

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_research_group_role_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE research_group_role_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE research_group_role_table
TO gomri_reader;
