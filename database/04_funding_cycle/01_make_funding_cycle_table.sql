-- -----------------------------------------------------------------------------
-- Name:      make_funding_cycle_table.sql
-- Author:    Patrick Krepps
-- Date:      06 August 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the funding cycle entity table and all
--            required elements of the table. The lack of proper constraints on
--            the data attributes (such as not validating a website URL to be a
--            proper URL) are a task requirement that I think will eventually
--            cause problems, but  I was told we would revisit this script and
--            associated scripts before they made it into production code.
--            12 August 2015, The Product Owner has indicated that ON DELETE
--            actions are to be restricted, not cascaded. Modified the script
--            to reflect this decision.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW funding_cycle;
DROP TABLE funding_cycle_table;

-- Now create funding_cycle_table, and make the necessary alterations:
CREATE TABLE funding_cycle_table
(
   funding_cycle_number                     SERIAL,
   funding_organization_number              INTEGER             NOT NULL,
   funding_cycle_creation_time              TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL, 
   funding_cycle_creator                    TEXT                NOT NULL,
   funding_cycle_description                TEXT                DEFAULT NULL,
   funding_cycle_end_date                   DATE                DEFAULT NULL,
   funding_cycle_modification_time          TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   funding_cycle_modifier                   TEXT                NOT NULL,
   funding_cycle_name                       TEXT                NOT NULL,
   funding_cycle_start_date                 DATE                DEFAULT NULL,
   funding_cycle_website                    TEXT                DEFAULT NULL,

   CONSTRAINT fk_funding_cycle_funding_organization
      FOREIGN KEY (funding_organization_number)
      REFERENCES funding_organization_table(funding_organization_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,
   CONSTRAINT chk_fc_end_date_not_before_start
      CHECK (funding_cycle_end_date >=
             funding_cycle_start_date + INTERVAL '1 DAY'),

   CONSTRAINT chk_fc_mod_time_not_before_create
      CHECK(funding_cycle_modification_time >= funding_cycle_creation_time),

   PRIMARY KEY (funding_cycle_number)
);

ALTER INDEX funding_cycle_table_pkey
   RENAME TO uidx_pk_funding_cycle;

ALTER SEQUENCE funding_cycle_table_funding_cycle_number_seq
   RENAME TO seq_funding_cycle_number;

ALTER SEQUENCE seq_funding_cycle_number
   START 1;

-- Set object ownership:
ALTER TABLE funding_cycle_table
   OWNER TO gomri_admin;

-- Enforce name/FO uniqueness:
CREATE UNIQUE INDEX uidx_funding_cycle2funding_org
   ON funding_cycle_table(LOWER(funding_cycle_name),
                          funding_organization_number);

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_funding_cycle_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_cycle_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_cycle_table
TO gomri_reader;
