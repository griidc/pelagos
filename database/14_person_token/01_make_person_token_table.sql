-- -----------------------------------------------------------------------------
-- Name:      make_person_token.sql
-- Author:    Patrick Krepps
-- Date:      06 November 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the person_token entity table and all
--            required elements of the table. By using the person number FK as
--            the person_token PK we implicitely enforce the rule that a person
--            can never have more than one outstanding token.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW IF EXISTS person_token;
DROP TABLE IF EXISTS person_token_table CASCADE;
DROP DOMAIN IF EXISTS TOKEN_TYPE;
DROP TYPE IF EXISTS TOKEN_USE_TYPE CASCADE;

-- Create the custom data types:
CREATE DOMAIN TOKEN_TYPE
AS CHAR(64)
CONSTRAINT CHK_TOKEN
   CHECK (VALUE ~* '^[a-f0-9]{64}$');

CREATE TYPE TOKEN_USE_TYPE
AS ENUM ('CREATE_ACCOUNT',
         'RESET_ACCOUNT');

-- Now create person_token_table:
CREATE TABLE person_token_table
(
   person_number                            INTEGER             NOT NULL,
   person_token_creation_time               TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   person_token_creator                     TEXT                NOT NULL,
   person_token_token                       TOKEN_TYPE          NOT NULL,
   person_token_use                         TOKEN_USE_TYPE      NOT NULL,
   person_token_valid_for                   INTERVAL            NOT NULL
);

ALTER TABLE person_token_table
   ADD CONSTRAINT uidx_pk_person_token
      PRIMARY KEY (person_number);

ALTER TABLE person_token_table
   ADD CONSTRAINT chk_valid_for
      CHECK (person_token_valid_for <= '7 DAYS');

ALTER TABLE person_token_table
   ADD CONSTRAINT fk_person_token_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

-- The odds of a randomly generated 256 bit number being duplicated during the
-- lifetime of a token are pretty darn slim, but this table won't generate
-- enough activity such that creating a unique index to verify the token can
-- never be used more than once is likely to be a concern.
CREATE UNIQUE INDEX uidx_person_token_token
   ON person_token_table(person_token_token);

-- Set object ownership:
ALTER TABLE person_token_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE person_token_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE person_token_table
TO gomri_reader;
