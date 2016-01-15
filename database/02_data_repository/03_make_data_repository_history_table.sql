-- -----------------------------------------------------------------------------
-- Name:      make_data_repository.sql
-- Author:    Patrick Krepps
-- Date:      29 October 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the data_repository history table that will
--            maintain a history of changes to the data_repository view's
--            attributes. The attributes of this table are defined in the same
--            order as they appear in the data_repository view instead of the
--            standard order (PK(s) first, (FK(s)), attributes in US English
--            alphabetical order).
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the history table:
DROP TABLE IF EXISTS data_repository_history_table CASCADE;

-- Create the data_repository_history_table with a surrogate key and all
-- data_repository attributes of interest. The attribute names remain the
-- same as the names are in the data_repository view:
CREATE TABLE data_repository_history_table
(
   data_repository_history_number           SERIAL,
   data_repository_history_action           TEXT,
   data_repository_number                   INTEGER             NOT NULL,
   name                                     TEXT,
   description                              TEXT,
   creator                                  TEXT                NOT NULL,
   creation_time                            TIMESTAMP WITH TIME ZONE
      NOT NULL,
   phone_number                             TEXT,
   email_address                            EMAIL_ADDRESS_TYPE,
   website                                  TEXT,
   delivery_point                           TEXT,
   city                                     TEXT,
   administrative_area                      TEXT,
   country                                  TEXT,
   postal_code                              TEXT,
   modifier                                 TEXT                NOT NULL,
   modification_time                        TIMESTAMP WITH TIME ZONE
      NOT NULL,

   PRIMARY KEY (data_repository_history_number)
);

ALTER INDEX data_repository_history_table_pkey
   RENAME TO uidx_pk_data_repository_history;

-- Note the truncation of data_repository to make the sequence name a bit
-- less unwieldy to deal with:
ALTER SEQUENCE data_repository_history_table_data_repository_history_numbe_seq
   RENAME TO seq_data_repository_history_number;

ALTER SEQUENCE seq_data_repository_history_number
   START 1;

-- Testing should be done to verify this index will be used before implementing
-- it. My gut feeling is that it will not, so it is commented out for now:
-- CREATE INDEX idx_data_repository_history_surname
--    ON data_repository_history_table(surname);

-- Set object ownership:
ALTER TABLE data_repository_history_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_data_repository_history_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE data_repository_history_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE data_repository_history_table
TO gomri_reader;
