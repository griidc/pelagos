-- -----------------------------------------------------------------------------
-- Name:      make_funding_cycle_history_table.sql
-- Author:    Patrick Krepps
-- Date:      06 August 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the funding_cycle history table that will
--            maintain a history of changes to the funding_cycle view's
--            attributes.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the history table:
DROP TABLE IF EXISTS funding_cycle_history_table;

-- Create the funding_cycle_history_table with a surrogate key and all
-- funding_cycle attributes of interest. The attribute names remain the
-- same as the names are in the funding_cycle view:
CREATE TABLE funding_cycle_history_table
(
   funding_cycle_history_number             SERIAL,
   funding_cycle_history_action             TEXT                NOT NULL,
   funding_cycle_number                     INTEGER             NOT NULL,
   name                                     TEXT,
   description                              TEXT,
   start_date                               DATE,
   end_date                                 DATE,
   website                                  TEXT,
   funding_organization_number              INTEGER             NOT NULL,
   creator                                  TEXT                NOT NULL,
   creation_time                            TIMESTAMP WITH TIME ZONE
      NOT NULL,
   modifier                                 TEXT                NOT NULL,
   modification_time                        TIMESTAMP WITH TIME ZONE
      NOT NULL,

   PRIMARY KEY (funding_cycle_history_number)
);

ALTER INDEX funding_cycle_history_table_pkey
   RENAME TO uidx_pk_funding_cycle_history;

-- Note the truncation of funding_cycle to make the sequence name a bit
-- less unwieldy to deal with:
ALTER SEQUENCE funding_cycle_history_table_funding_cycle_history_number_seq
   RENAME TO seq_funding_cycle_history_number;

ALTER SEQUENCE seq_funding_cycle_history_number
   START 1;

-- Testing should be done to verify this index will be used before implementing
-- it. My gut feeling is that it will not, so it is commented out for now:
-- CREATE INDEX idx_funding_cycle_history_surname
--    ON funding_cycle_history_table(surname);

-- Set object ownership:
ALTER TABLE funding_cycle_history_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_funding_cycle_history_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_cycle_history_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_cycle_history_table
TO gomri_reader;
