-- -----------------------------------------------------------------------------
-- Name:      make_person.sql
-- Author:    Patrick Krepps
-- Date:      23 June 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the person history table that will maintain a
--            history of changes to the person view's attributes. The
--            attributes of this table are defined in the same order as they
--            appear in the person view instead of the standard (PK(s) first,
--            (FK(s)), attributes in US English alphabetical order).
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the history table:
DROP TABLE person_history_table

-- Create the person_history_table with a surrogate key and all person
-- attributes of interest. The attribute names remain the same as the names
-- are in the person view:
CREATE TABLE person_history_table
(
   person_history_number                    SERIAL,
   person_history_action                    TEXT,
   person_number                            INTEGER             NOT NULL,
   title                                    TEXT,
   given_name                               TEXT,
   middle_name                              TEXT,
   surname                                  TEXT,
   email_address                            EMAIL_ADDRESS_TYPE,
   modifier                                 TEXT                NOT NULL,
   modification_time                        TIMESTAMP WITH TIME ZONE
      NOT NULL,

   PRIMARY KEY (person_history_number)
);

ALTER INDEX person_history_table_pkey
   RENAME TO uidx_pk_person_history;

ALTER SEQUENCE person_history_table_person_history_number_seq
   RENAME TO seq_person_history_number;

ALTER SEQUENCE seq_person_history_number
   START 1;

-- Testing should be done to verify this index will be used before implementing
-- it. My gut feeling is that it will not, so it is commented out for now:
-- CREATE INDEX idx_person_history_surname
--    ON person_history_table(surname);

-- Set object ownerships:
ALTER TABLE person_history_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_person_history_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_history_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_history_table
TO gomri_reader;
