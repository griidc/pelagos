-- -----------------------------------------------------------------------------
-- Name:      make_email2research_group.sql
-- Author:    Patrick Krepps
-- Date:      16 September 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the email2research_group relationship table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the table (ignore warnings if it does not exist)
DROP TABLE email2research_group_table;
CREATE TABLE email2research_group_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   research_group_number                    INTEGER             NOT NULL,

   CONSTRAINT fk_email2research_group_email_address
      FOREIGN KEY (email_address)
      REFERENCES email_table(email_address)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   -- Note the truncation of research_group to make the constraint name
   -- less unwieldy to deal with:
   CONSTRAINT fk_email2research_group_fg_number
      FOREIGN KEY (research_group_number)
      REFERENCES research_group_table(research_group_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (email_address,
                research_group_number)
);

ALTER INDEX email2research_group_table_pkey
   RENAME TO uidx_pk_email2research_group;

CREATE UNIQUE INDEX uidx_one_email_to_rg
   ON email2research_group_table(research_group_number);

-- Set object ownership's:
ALTER TABLE email2research_group_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE email2research_group_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE email2research_group_table
TO gomri_reader;
