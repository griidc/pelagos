-- -----------------------------------------------------------------------------
-- Name:      make_email2funding_organization.sql
-- Author:    Patrick Krepps
-- Date:      05 May 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the email2funding_organization relationship
--            table.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping the table (ignore warnings if it does not exist)
DROP TABLE IF EXISTS email2funding_organization_table;
CREATE TABLE email2funding_organization_table
(
   email_address                            EMAIL_ADDRESS_TYPE  NOT NULL,
   funding_organization_number              INTEGER             NOT NULL,

   CONSTRAINT fk_email2funding_organization_email_address
      FOREIGN KEY (email_address)
      REFERENCES email_table(email_address)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   -- Note the truncation of funding_organization to make the constraint name
   -- less unwieldy to deal with:
   CONSTRAINT fk_email2funding_organization_fund_org_number
      FOREIGN KEY (funding_organization_number)
      REFERENCES funding_organization_table(funding_organization_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT,

   PRIMARY KEY (email_address,
                funding_organization_number)
);

ALTER INDEX email2funding_organization_table_pkey
   RENAME TO uidx_pk_email2funding_organization;

CREATE UNIQUE INDEX uidx_one_email_to_fund_org
   ON email2funding_organization_table(funding_organization_number);

-- Set object ownership's:
ALTER TABLE email2funding_organization_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE email2funding_organization_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE email2funding_organization_table
TO gomri_reader;
