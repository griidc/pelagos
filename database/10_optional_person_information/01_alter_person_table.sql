-- -----------------------------------------------------------------------------
-- Name:      alter_person_table.sql
-- Author:    Patrick Krepps
-- Date:      21 October 2015
-- Inputs:    NONE
-- Output:    A modified permanent database table
-- Info:      This script modifies the person entity table to add the optional
--            information attributes.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything (thecw columns need to be done individually
-- because if any one does not exist then the entire statement would fail):
DROP VIEW person;
ALTER TABLE person_table
   DROP COLUMN person_administrative_area;
ALTER TABLE person_table
   DROP COLUMN person_city;
ALTER TABLE person_table
   DROP COLUMN person_country;
ALTER TABLE person_table
   DROP COLUMN person_delivery_point;
ALTER TABLE person_table
   DROP COLUMN person_organization;
ALTER TABLE person_table
   DROP COLUMN person_phone_number;
ALTER TABLE person_table
   DROP COLUMN person_position;
ALTER TABLE person_table
   DROP COLUMN person_postal_code;
ALTER TABLE person_table
   DROP COLUMN person_website;
DROP DOMAIN PHONE_NUMBER_TYPE;

-- Now create the telephone number type:
CREATE DOMAIN PHONE_NUMBER_TYPE
AS TEXT
DEFAULT NULL
CONSTRAINT chk_phone_number
   CHECK (VALUE ~ '^[1-9][0-9]{2}[1-9][0-9]{6}$');

-- Now add the new attributes:
ALTER TABLE person_table
   ADD COLUMN person_administrative_area    TEXT,
   ADD COLUMN person_city                   TEXT,
   ADD COLUMN person_country                TEXT,
   ADD COLUMN person_delivery_point         TEXT,
   ADD COLUMN person_organization           TEXT,
   ADD COLUMN person_phone_number           PHONE_NUMBER_TYPE,
   ADD COLUMN person_position               TEXT,
   ADD COLUMN person_postal_code            TEXT,
   ADD COLUMN person_website                TEXT;

