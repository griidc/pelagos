-- -----------------------------------------------------------------------------
-- Name:      make_account.sql
-- Author:    Patrick Krepps
-- Date:      05 November 2015
-- Inputs:    NONE
-- Output:    A new database entity
-- Info:      This script creates the account entity table and all required
--            elements of the table. By using the person number FK as the user
--            PK we implicitely enforce the rule that a person can have at the
--            most a single user ID.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------
\c gomri postgres

-- Start by dropping everything:
DROP VIEW IF EXISTS account;
DROP TABLE IF EXISTS account_table CASCADE;
DROP TYPE IF EXISTS HASH_ALGORITHM_TYPE CASCADE;
DROP TYPE IF EXISTS USER_ID_TYPE CASCADE;

-- Create the custom data type, The HASH_ALGORITHM represents possible LDAP
-- values (excluding CRYPT derived values):
CREATE TYPE HASH_ALGORITHM_TYPE
AS ENUM ('MD5',
         'SHA',
         'SMD5',
         'SSHA');

CREATE DOMAIN USER_ID_TYPE
AS TEXT
CONSTRAINT chk_user_id
   CHECK (VALUE ~ '^[a-z][a-z0-9]{1,31}$');

-- Now create account_table:
CREATE TABLE account_table
(
   person_number                            INTEGER             NOT NULL,
   account_creation_time                    TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   account_creator                          TEXT                NOT NULL,
   account_hash_algorithm                   HASH_ALGORITHM_TYPE NOT NULL
      DEFAULT 'SSHA',
   account_modification_time                TIMESTAMP WITH TIME ZONE
      DEFAULT DATE_TRUNC('seconds', NOW())  NOT NULL,
   account_modifier                         TEXT                NOT NULL,
   account_password                         BYTEA               NOT NULL,
   account_password_salt                    BYTEA               NOT NULL,
   account_user_id                          USER_ID_TYPE        NOT NULL
);

ALTER TABLE account_table
   ADD CONSTRAINT uidx_pk_account
      PRIMARY KEY (person_number);

ALTER TABLE account_table
   ADD CONSTRAINT chk_account_mod_time_not_before_create
      CHECK (account_modification_time >= account_creation_time);

ALTER TABLE account_table
   ADD CONSTRAINT fk_account_person_number
      FOREIGN KEY (person_number)
      REFERENCES person_table(person_number)
      ON DELETE RESTRICT
      ON UPDATE RESTRICT;

CREATE UNIQUE INDEX uidx_account_user_id
   ON account_table(account_user_id);

-- Set object ownership:
ALTER TABLE account_table
   OWNER TO gomri_admin;

-- Set the other permissions:
GRANT DELETE,
      INSERT,
      SELECT,
      UPDATE
ON TABLE account_table
TO gomri_user,
   gomri_writer;

GRANT SELECT
ON TABLE account_table
TO gomri_reader;
