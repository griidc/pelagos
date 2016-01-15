-- -----------------------------------------------------------------------------
-- Name:      make_funding_organization_role.sql
-- Author:    Patrick Krepps
-- Date:      14 Oct 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the funding_organization_role table and all
--            required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Drop everything to start with:
DROP VIEW IF EXISTS funding_organization_role;
DROP VIEW IF EXISTS person2funding_organization2role;
DROP TABLE IF EXISTS person2funding_organization2role_table;
DROP TABLE IF EXISTS funding_organization_role_table;

-- Create the table:
CREATE TABLE funding_organization_role_table
(
   funding_organization_role_number         SERIAL              NOT NULL,
   funding_organization_role_creation_time  TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   funding_organization_role_creator        TEXT                NOT NULL,
   funding_organization_role_modification_time    TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   funding_organization_role_modifier       TEXT                NOT NULL,
   funding_organization_role_name           TEXT                NOT NULL,
   funding_organization_role_weight         INTEGER             NOT NULL
);

ALTER TABLE funding_organization_role_table
   ADD CONSTRAINT uidx_pk_funding_organization_role_table
      PRIMARY KEY (funding_organization_role_number);

ALTER SEQUENCE funding_organization_role_tab_funding_organization_role_num_seq
   RENAME TO seq_funding_organization_role_number;

ALTER SEQUENCE seq_funding_organization_role_number
   START 1;

ALTER TABLE funding_organization_role_table
   OWNER TO gomri_admin;

CREATE UNIQUE INDEX uidx_funding_organization_role_name
   ON funding_organization_role_table(LOWER(funding_organization_role_name));

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_funding_organization_role_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_organization_role_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_organization_role_table
TO gomri_reader;
