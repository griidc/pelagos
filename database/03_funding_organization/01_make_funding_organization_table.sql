-- -----------------------------------------------------------------------------
-- Name:      make_funding_organization_table.sql
-- Author:    Patrick Krepps
-- Date:      09 July 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the funding organization entity table and all
--            required elements of the table. The lack of data constraints on
--            the non-normalized attributes are a task requirement that I think
--            will cause problems down the road, but I was told we would
--            revisit this and associated scripts before they made it into
--            production code. Fortunately the choice to abstract the physical
--            entities out to views will allow the necessary normalization
--            practices to be implemented at that time with minimal disruption.
--            This table will most likely ever contain more than a handful of
--            rows, so no index is going to be worth the trouble.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW funding_organization;
DROP TABLE email2funding_organization_table;
DROP TABLE funding_organization_table;

-- Now create funding_organization_table, and make the necessary alterations:
CREATE TABLE funding_organization_table
(
   funding_organization_number              SERIAL,
   funding_organization_administrative_area TEXT                DEFAULT NULL,
   funding_organization_city                TEXT                DEFAULT NULL,
   funding_organization_country             TEXT                DEFAULT NULL,
   funding_organization_delivery_point      TEXT                DEFAULT NULL,
   funding_organization_description         TEXT                DEFAULT NULL,
   funding_organization_logo                BYTEA               DEFAULT NULL,
   funding_organization_name                TEXT                NOT NULL,
   funding_organization_phone_number        TEXT                DEFAULT NULL,
   funding_organization_postal_code         TEXT                DEFAULT NULL,
   funding_organization_website             TEXT                DEFAULT NULL,

   PRIMARY KEY (funding_organization_number)
);

ALTER INDEX funding_organization_table_pkey
   RENAME TO uidx_pk_funding_organization;

ALTER SEQUENCE funding_organization_table_funding_organization_number_seq
   RENAME TO seq_funding_organization_number;

ALTER SEQUENCE seq_funding_organization_number
   START 1;

-- Set object ownership:
ALTER TABLE funding_organization_table
   OWNER TO gomri_admin;

-- Enforce name uniqueness:
CREATE UNIQUE INDEX uidx_lower_funding_organization
   ON funding_organization_table(LOWER(funding_organization_name));

-- Set the other permissions:
GRANT USAGE
ON SEQUENCE seq_funding_organization_number
TO gomri_reader,
   gomri_user,
   gomri_writer;

GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE funding_organization_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE funding_organization_table
TO gomri_reader;
