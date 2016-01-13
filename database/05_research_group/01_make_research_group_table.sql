-- -----------------------------------------------------------------------------
-- Name:      make_research_group_table.sql
-- Author:    Patrick Krepps
-- Date:      16 September 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the research group entity table and all
--            required elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP TABLE IF EXISTS email2research_group_table;
DROP VIEW IF EXISTS research_group;
-- CASCADE due to fk_person2research_group2role_rg_number on table person2research_group2role_table
DROP TABLE IF EXISTS research_group_table CASCADE;

-- Now create research_group_table, and make the necessary alterations:
CREATE TABLE research_group_table
(
   research_group_number                    SERIAL,
   funding_cycle_number                     INTEGER             NOT NULL,
   research_group_administrative_area       TEXT                DEFAULT NULL,
   research_group_city                      TEXT                DEFAULT NULL,
   research_group_country                   TEXT                DEFAULT NULL,
   research_group_creation_time             TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   research_group_creator                   TEXT                NOT NULL,
   research_group_delivery_point            TEXT                DEFAULT NULL,
   research_group_description               TEXT                DEFAULT NULL,
--    research_group_end_date                  DATE                DEFAULT NULL,
   research_group_logo                      BYTEA               DEFAULT NULL,
   research_group_modification_time         TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   research_group_modifier                  TEXT                NOT NULL,
   research_group_name                      TEXT                NOT NULL,
   research_group_phone_number              TEXT                DEFAULT NULL,
   research_group_postal_code               TEXT                DEFAULT NULL,
--    research_group_start_date                DATE                DEFAULT NULL,
   research_group_website                   TEXT                DEFAULT NULL,

   CONSTRAINT fk_research_group_funding_cycle
      FOREIGN KEY (funding_cycle_number)
      REFERENCES funding_cycle_table(funding_cycle_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,
--    CONSTRAINT chk_rg_end_date_not_before_start
--       CHECK (research_group_end_date >=
--              research_group_start_date + INTERVAL '1 DAY'),

   CONSTRAINT chk_fc_mod_time_not_before_create
      CHECK(research_group_modification_time >= research_group_creation_time),

   PRIMARY KEY (research_group_number)
);

ALTER INDEX research_group_table_pkey
   RENAME TO uidx_pk_research_group;

ALTER SEQUENCE research_group_table_research_group_number_seq
   RENAME TO seq_research_group_number;

ALTER SEQUENCE seq_research_group_number
   START 1;

-- Set object ownership:
ALTER TABLE research_group_table
   OWNER TO gomri_admin;

-- Enforce name/FO uniqueness:
CREATE UNIQUE INDEX uidx_research_group2funding_org
   ON research_group_table(LOWER(research_group_name),
                          funding_cycle_number);

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_research_group_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE research_group_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE research_group_table
TO gomri_reader;
