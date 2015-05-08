-- -----------------------------------------------------------------------------
-- Name:      make_email.sql
-- Author:    Patrick Krepps
-- Date:      05 May 2015
-- Inputs:    NONE
-- Output:    A new database table
-- Info:      This script creates the email entity table and all required
--            elements of the table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP TABLE email_table CASCADE;

-- A case insensitive email data type that also matches IP addresses as the
-- domain (note that RFC 5322 [http://www.rfc-editor.org/rfc/rfc5322.txt]
-- allows almost all non-whitespace characters to be used in the local part. We
-- are just allowing alphanumeric characters, an underscore, a hyphen, or a
-- period at the moment):
CREATE DOMAIN EMAIL_ADDRESS_TYPE AS TEXT
CONSTRAINT chk_email
   CHECK (VALUE ~* '^[\w.-]+@([\w-]+\.)*[\w-]+\.[a-z]{2,}$');

CREATE TABLE email_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   email_validated                          BOOLEAN             NOT NULL
      DEFAULT FALSE,

   PRIMARY KEY (email_address)
);

ALTER INDEX email_table_pkey
   RENAME TO uidx_pk_email;

-- Set object ownerships:
ALTER TABLE email_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE email_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE email_table
TO gomri_reader;
