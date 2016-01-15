-- -----------------------------------------------------------------------------
-- Name:      make_email2person.sql
-- Author:    Patrick Krepps
-- Date:      05 May 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the email2person relationship table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the table (ignore warnings if it does not exist)
DROP TABLE IF EXISTS email2person_table CASCADE;
CREATE TABLE email2person_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   person_number                            INTEGER             NOT NULL,
   is_primary_email_address                 BOOLEAN             NOT NULL
      DEFAULT FALSE,

   CONSTRAINT fk_email2person_email_address
      FOREIGN KEY (email_address)
      REFERENCES email_table(email_address)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   CONSTRAINT fk_email2person_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (email_address,
                person_number)
);

ALTER INDEX email2person_table_pkey
   RENAME TO uidx_pk_email2person;

CREATE UNIQUE INDEX uidx_one_primary_per_person
   ON email2person_table (person_number,
                          is_primary_email_address)
   WHERE is_primary_email_address = TRUE;

-- Set object ownership:
ALTER TABLE email2person_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE email2person_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE email2person_table
TO gomri_reader;
