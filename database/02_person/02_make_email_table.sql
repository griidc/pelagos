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

-- A case insensitive email data type that allows any character other than a
-- dot, an @, or any whitespace, followed by any number of characters that are
-- not an @ or whitespace, followed by an @, a domain name that cannot consist
-- of an @ or whitespace, anchored by a TLD that can contain anything other
-- than whitespace or an @ (Note that RFC 5322 allows local parts to contain
-- whitespace in quoted strings, but we are choosing to disallow that):
CREATE DOMAIN EMAIL_ADDRESS_TYPE AS TEXT
CONSTRAINT chk_email
   CHECK (VALUE ~* '^[^.@\s][^@\s]*@[^@\s]+\.[^@\s]+$');

CREATE TABLE email_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   email_validated                          BOOLEAN             NOT NULL
      DEFAULT FALSE,

   PRIMARY KEY (email_address)
);

ALTER INDEX email_table_pkey
   RENAME TO uidx_pk_email;

-- Create a UNIQUE INDEX on the lower case value of email address so we can
-- maintain case-insensitive uniqueness of email address. This violates RFC
-- 5321 specification that local-parts are to be treated in a case-sensitive
-- manner, but the RFC also indicates this requirement is discouraged
-- (http://tools.ietf.org/html/rfc5321#section-2.4):
CREATE UNIQUE INDEX uidx_lower_email
   ON email_table (LOWER(email_address));

-- Set object ownership:
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
