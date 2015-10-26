-- -----------------------------------------------------------------------------
-- Name:      alter_person_history_table.sql
-- Author:    Patrick Krepps
-- Date:      21 October 2015
-- Inputs:    NONE
-- Output:    A modified permanent database table
-- Info:      This script modifies the person_history table to add the optional
--            information attributes.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything (these need to be done individually because if
-- any one does not exist then the entire statement would fail):
ALTER TABLE person_history_table
   DROP COLUMN administrative_area;
ALTER TABLE person_history_table
   DROP COLUMN city;
ALTER TABLE person_history_table
   DROP COLUMN country;
ALTER TABLE person_history_table
   DROP COLUMN delivery_point;
ALTER TABLE person_history_table
   DROP COLUMN organization;
ALTER TABLE person_history_table
   DROP COLUMN phone_number;
ALTER TABLE person_history_table
   DROP COLUMN position;
ALTER TABLE person_history_table
   DROP COLUMN postal_code;
ALTER TABLE person_history_table
   DROP COLUMN website;

-- Now add the new attributes (phone_number is stored as TEXT since the
-- validity of the data would have been confirmed when stored in the people
-- table column):
ALTER TABLE person_history_table
   ADD COLUMN administrative_area    TEXT,
   ADD COLUMN city                   TEXT,
   ADD COLUMN country                TEXT,
   ADD COLUMN delivery_point         TEXT,
   ADD COLUMN organization           TEXT,
   ADD COLUMN phone_number           TEXT,
   ADD COLUMN position               TEXT,
   ADD COLUMN postal_code            TEXT,
   ADD COLUMN website                TEXT;
