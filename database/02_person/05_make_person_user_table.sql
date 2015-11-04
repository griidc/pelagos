-- -----------------------------------------------------------------------------
-- Name:      make_user.sql
-- Author:    Patrick Krepps
-- Date:      29 October 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the user entity table and all required
--            elements of the table. By using the person number FK as the user
--            PK we implicitely enforce the rule that a person can have at the
--            most a single user ID.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW person_user;
DROP TABLE person_user_table CASCADE;

-- Now create person_user_table:
CREATE TABLE person_user_table
(
   person_number                            INTEGER             NOT NULL,
   person_user_creation_time                TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person_user_creator                      TEXT                NOT NULL,
   person_user_id                           TEXT                NOT NULL,
   person_user_modification_time            TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person_user_modifier                     TEXT                NOT NULL
);

ALTER TABLE person_user_table
   ADD CONSTRAINT uidx_pk_person_number
      PRIMARY KEY (person_number);

ALTER TABLE person_user_table
   ADD CONSTRAINT chk_person_user_mod_time_not_before_create
      CHECK (person_user_modification_time >= person_user_creation_time);

ALTER TABLE person_user_table
   ADD CONSTRAINT fk_person_user_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

CREATE UNIQUE INDEX uidx_person_user_id
   ON person_user_table(LOWER(person_user_id));

-- Set object ownership:
ALTER TABLE person_user_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_user_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_user_table
TO gomri_reader;
