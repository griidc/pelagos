-- -----------------------------------------------------------------------------
-- Name:      make_funding_organization.sql
-- Author:    Patrick Krepps
-- Date:      09 July 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the funding_organization history table that
--            will maintain a history of changes to the funding_organization
--            view's attributes. The attributes of this table are defined in
--            the same order as they appear in the funding_organization view
--            instead of the standard order (PK(s) first, (FK(s)), attributes
--            in US English alphabetical order).
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the history table:
DROP TABLE IF EXISTS funding_organization_history_table;

-- Create the funding_organization_history_table with a surrogate key and all
-- funding_organization attributes of interest. The attribute names remain the
-- same as the names are in the funding_organization view:
CREATE TABLE funding_organization_history_table
(
   funding_organization_history_number      SERIAL,
   funding_organization_history_action      TEXT,
   funding_organization_number              INTEGER             NOT NULL,
   name                                     TEXT,
   description                              TEXT,
   data_repository_number                   INTEGER             NOT NULL,
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
   logo                                     BYTEA,
   modifier                                 TEXT                NOT NULL,
   modification_time                        TIMESTAMP WITH TIME ZONE
      NOT NULL,

   PRIMARY KEY (funding_organization_history_number)
);

ALTER INDEX funding_organization_history_table_pkey
   RENAME TO uidx_pk_funding_organization_history;

-- Note the truncation of funding_organization to make the sequence name a bit
-- less unwieldy to deal with:
ALTER SEQUENCE funding_organization_history__funding_organization_history__seq
   RENAME TO seq_funding_organization_history_number;

ALTER SEQUENCE seq_funding_organization_history_number
   START 1;

-- Testing should be done to verify this index will be used before implementing
-- it. My gut feeling is that it will not, so it is commented out for now:
-- CREATE INDEX idx_funding_organization_history_surname
--    ON funding_organization_history_table(surname);

-- Set object ownership:
ALTER TABLE funding_organization_history_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_funding_organization_history_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_organization_history_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_organization_history_table
TO gomri_reader;
