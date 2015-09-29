-- -----------------------------------------------------------------------------
-- Name:      make_research_group_history_table.sql
-- Author:    Patrick Krepps
-- Date:      16 September 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the research_group history table that will
--            maintain a history of changes to the research_group view's
--            attributes.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the history table:
DROP TABLE research_group_history_table;

-- Create the research_group_history_table with a surrogate key and all
-- research_group attributes of interest. The attribute names remain the
-- same as the names are in the research_group view:
CREATE TABLE research_group_history_table
(
   research_group_history_number            SERIAL,
   research_group_history_action            TEXT                NOT NULL,
   research_group_number                    INTEGER             NOT NULL,
   name                                     TEXT,
   description                              TEXT,
   funding_cycle_number                     INTEGER             NOT NULL,
--    start_date                               DATE,
--    end_date                                 DATE,
   phone_number                             TEXT,
   email_address                            EMAIL_ADDRESS_TYPE,
   website                                  TEXT,
   delivery_point                           TEXT,
   city                                     TEXT,
   administrative_area                      TEXT,
   country                                  TEXT,
   postal_code                              TEXT,
   logo                                     BYTEA,
   creator                                  TEXT                NOT NULL,
   creation_time                            TIMESTAMP WITH TIME ZONE
      NOT NULL,
   modifier                                 TEXT                NOT NULL,
   modification_time                        TIMESTAMP WITH TIME ZONE
      NOT NULL,

   PRIMARY KEY (research_group_history_number)
);

ALTER INDEX research_group_history_table_pkey
   RENAME TO uidx_pk_research_group_history;

-- Note the truncation of research_group to make the sequence name a bit
-- less unwieldy to deal with:
ALTER SEQUENCE research_group_history_table_research_group_history_number_seq
   RENAME TO seq_research_group_history_number;

ALTER SEQUENCE seq_research_group_history_number
   START 1;

-- Testing should be done to verify this index will be used before implementing
-- it. My gut feeling is that it will not, so it is commented out for now:
-- CREATE INDEX idx_research_group_history_surname
--    ON research_group_history_table(surname);

-- Set object ownership:
ALTER TABLE research_group_history_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_research_group_history_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE research_group_history_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE research_group_history_table
TO gomri_reader;
