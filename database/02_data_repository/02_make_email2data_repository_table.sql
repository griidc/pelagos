-- -----------------------------------------------------------------------------
-- Name:      make_email2data_repository.sql
-- Author:    Patrick Krepps
-- Date:      29 October 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the email2data_repository relationship
--            table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the table (ignore warnings if it does not exist)
DROP TABLE IF EXISTS email2data_repository_table CASCADE;

CREATE TABLE email2data_repository_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   data_repository_number                   INTEGER             NOT NULL,

   CONSTRAINT fk_email2data_repository_email_address
      FOREIGN KEY (email_address)
      REFERENCES email_table(email_address)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   -- Note the truncation of data_repository to make the constraint name
   -- less unwieldy to deal with:
   CONSTRAINT fk_email2data_repository_data_repository_number
      FOREIGN KEY (data_repository_number)
      REFERENCES data_repository_table(data_repository_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (email_address,
                data_repository_number)
);

ALTER INDEX email2data_repository_table_pkey
   RENAME TO uidx_pk_email2data_repository;

CREATE UNIQUE INDEX uidx_one_email_to_data_repository
   ON email2data_repository_table(data_repository_number);

-- Set object ownership's:
ALTER TABLE email2data_repository_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE email2data_repository_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE email2data_repository_table
TO gomri_reader;
